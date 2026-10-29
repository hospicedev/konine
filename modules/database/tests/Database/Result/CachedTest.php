<?php

/**
 * Test case for Kohana_Database_Result_Cached
 *
 * @package    Kohana/Database
 * @group      kohana
 * @group      kohana.database
 * @category   Test
 * @author     Kohana Team
 * @copyright  (c) Kohana Team
 * @license    https://github.com/hospicedev/konine/blob/master/LICENSE.md
 */

class Kohana_Database_Result_CachedTest extends Unittest_TestCase
{
	/**
	 * Rows used to build cached results in these tests
	 * @var array
	 */
	protected $rows = [
		['id' => 1, 'name' => 'one'],
		['id' => 2, 'name' => 'two'],
	];

	/**
	 * seek() and current() implement SeekableIterator/Iterator without native
	 * return types, so they must carry #[\ReturnTypeWillChange]. Without it
	 * PHP 8.1+ raises E_DEPRECATED when the class is linked, which
	 * Kohana::error_handler() turns into a fatal error under E_ALL.
	 *
	 * Runs in a separate process because the class may already be loaded by
	 * other tests.
	 *
	 * @test
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 * @see https://github.com/hospicedev/konine/issues/16
	 */
	public function test_loading_class_raises_no_deprecations()
	{
		$this->assertFalse(
			class_exists('Kohana_Database_Result_Cached', FALSE),
			'Class must not be loaded before the deprecation check runs'
		);

		$deprecations = [];

		error_reporting(E_ALL);

		set_error_handler(function ($code, $error, $file, $line) use (&$deprecations) {
			$deprecations[] = $error.' in '.$file.':'.$line;

			return TRUE;
		}, E_DEPRECATED);

		try
		{
			$this->assertTrue(class_exists('Database_Result_Cached'));
		}
		finally
		{
			restore_error_handler();
		}

		$this->assertSame([], $deprecations);
	}

	/**
	 * seek() must keep returning a boolean: callers such as
	 * Database_MySQLi_Result::current() and application code rely on it.
	 *
	 * @test
	 * @covers Database_Result_Cached::seek
	 */
	public function test_seek_returns_true_for_existing_offset()
	{
		$result = new Database_Result_Cached($this->rows, 'SELECT * FROM test');

		$this->assertTrue($result->seek(1));
		$this->assertSame(1, $result->key());
		$this->assertSame($this->rows[1], $result->current());
	}

	/**
	 * @test
	 * @covers Database_Result_Cached::seek
	 */
	public function test_seek_returns_false_for_missing_offset()
	{
		$result = new Database_Result_Cached($this->rows, 'SELECT * FROM test');

		$this->assertFalse($result->seek(5));
		$this->assertFalse($result->seek(-1));
		$this->assertSame(0, $result->key(), 'A failed seek must not move the current row');
	}

	/**
	 * @test
	 * @covers Database_Result_Cached::current
	 */
	public function test_current_returns_null_past_the_end()
	{
		$result = new Database_Result_Cached($this->rows, 'SELECT * FROM test');

		$result->seek(1);
		$result->next();

		$this->assertFalse($result->valid());
		$this->assertNull($result->current());
	}

	/**
	 * @test
	 * @covers Database_Result_Cached::current
	 */
	public function test_iterates_all_rows()
	{
		$result = new Database_Result_Cached($this->rows, 'SELECT * FROM test');

		$this->assertSame($this->rows, iterator_to_array($result));
		$this->assertCount(2, $result);
	}

	/**
	 * Cached results exist so they can be serialized into a cache.
	 *
	 * @test
	 * @covers Database_Result_Cached::seek
	 */
	public function test_survives_serialization()
	{
		$result = unserialize(serialize(new Database_Result_Cached($this->rows, 'SELECT * FROM test')));

		$this->assertTrue($result->seek(1));
		$this->assertSame($this->rows[1], $result->current());
	}

	/**
	 * Applications may extend Database_Result_Cached and override seek() and
	 * current() without return types; that must keep working.
	 *
	 * @test
	 * @covers Database_Result_Cached::seek
	 * @covers Database_Result_Cached::current
	 */
	public function test_untyped_subclass_overrides_still_work()
	{
		// Anonymous class so the test file does not load Database_Result_Cached
		// on include, which would defeat the deprecation check above
		$result = new class($this->rows, 'SELECT * FROM test') extends Database_Result_Cached {

			public function seek($offset)
			{
				return parent::seek($offset);
			}

			public function current()
			{
				return parent::current();
			}
		};

		$this->assertTrue($result->seek(1));
		$this->assertSame($this->rows[1], $result->current());
	}
}

