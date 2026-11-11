<?php

/**
 * Tests that as_object() constructor params behave the same on every
 * supported PHP version with the PDO driver.
 *
 * PHP 8.5 passes PDO::FETCH_CLASS constructor arguments with string keys as
 * named arguments. On PHP 8.4 they were positional and the keys were ignored.
 * Database_PDO keeps the PHP 8.4 behaviour.
 *
 * @package    Kohana/Database
 * @group      kohana
 * @group      kohana.database
 * @category   Test
 * @author     Kohana Team
 * @copyright  (c) Kohana Team
 * @license    https://github.com/hospicedev/konine/blob/master/LICENSE.md
 * @see        https://github.com/hospicedev/konine/issues/45
 */
class Kohana_Database_PDO_AsObjectTest extends Unittest_TestCase
{
	const INSTANCE = 'unittest_pdo_as_object';

	/**
	 * @var  Database_PDO
	 */
	protected $db;

	public function setUp(): void
	{
		parent::setUp();

		if ( ! extension_loaded('pdo_sqlite'))
		{
			$this->markTestSkipped('The pdo_sqlite extension is not available');
		}

		$this->db = Database::instance(self::INSTANCE, [
			'type'       => 'PDO',
			'connection' => [
				'dsn' => 'sqlite::memory:',
			],
			'table_prefix' => '',
			'charset'      => NULL,
		]);
	}

	public function tearDown(): void
	{
		if (isset(Database::$instances[self::INSTANCE]))
		{
			Database::$instances[self::INSTANCE]->disconnect();
			unset(Database::$instances[self::INSTANCE]);
		}

		parent::tearDown();
	}

	/**
	 * Provider for test_as_object_passes_params_positionally
	 *
	 * @return  array  of [$params, $expected_a, $expected_b], keyed by description
	 */
	public static function provider_as_object_params()
	{
		return [
			'no params' => [
				NULL, 'defA', 'defB',
			],
			'list' => [
				['x', 'y'], 'x', 'y',
			],
			'string keys in a different order' => [
				['b' => 'B', 'a' => 'A'], 'B', 'A',
			],
			'unknown string key' => [
				['foo' => 1], 1, 'defB',
			],
		];
	}

	/**
	 * @dataProvider provider_as_object_params
	 *
	 * @param   array|NULL  $params
	 * @param   mixed       $expected_a
	 * @param   mixed       $expected_b
	 * @return  void
	 */
	public function test_as_object_passes_params_positionally($params, $expected_a, $expected_b)
	{
		$rows = DB::query(Database::SELECT, 'SELECT 1 AS id')
			->as_object('Kohana_Database_PDO_AsObjectTest_Row', $params)
			->execute($this->db)
			->as_array();

		$this->assertCount(1, $rows);
		$this->assertInstanceOf('Kohana_Database_PDO_AsObjectTest_Row', $rows[0]);
		$this->assertEquals(1, $rows[0]->id);
		$this->assertSame($expected_a, $rows[0]->a);
		$this->assertSame($expected_b, $rows[0]->b);
	}

	/**
	 * Calling Database_PDO::query() directly behaves the same.
	 *
	 * @return  void
	 */
	public function test_query_passes_params_positionally()
	{
		$rows = $this->db
			->query(Database::SELECT, 'SELECT 1 AS id', 'Kohana_Database_PDO_AsObjectTest_Row', ['b' => 'B', 'a' => 'A'])
			->as_array();

		$this->assertSame('B', $rows[0]->a);
		$this->assertSame('A', $rows[0]->b);
	}

}

/**
 * Result class with constructor parameters, used by the tests above.
 */
class Kohana_Database_PDO_AsObjectTest_Row
{
	public $id;

	public $a;

	public $b;

	public function __construct($a = 'defA', $b = 'defB')
	{
		$this->a = $a;
		$this->b = $b;
	}
}
