<?php
include_once(Kohana::find_file('tests/cache', 'CacheBasicMethodsTest'));

/**
 * @package    Kohana/Cache
 * @group      kohana
 * @group      kohana.cache
 * @category   Test
 * @author     Kohana Team
 * @copyright  (c) Kohana Team
 * @license    https://github.com/hospicedev/konine/blob/master/LICENSE.md
 */
class Kohana_Cache_FileTest extends Kohana_CacheBasicMethodsTest {

	/**
	 * This method MUST be implemented by each driver to setup the `Cache`
	 * instance for each test.
	 * 
	 * This method should do the following tasks for each driver test:
	 * 
	 *  - Test the Cache instance driver is available, skip test otherwise
	 *  - Setup the Cache instance
	 *  - Call the parent setup method, `parent::setUp()`
	 *
	 * @return  void
	 */
	public function setUp(): void
	{
		parent::setUp();

		if ( ! Kohana::$config->load('cache.file'))
		{
			Kohana::$config->load('cache')
				->set(
					'file',
					[
						'driver'             => 'file',
						'cache_dir'          => APPPATH.'cache',
						'default_expire'     => 3600,
						'ignore_on_delete'   => [
							'file_we_want_to_keep.cache',
							'.gitignore',
							'.git',
							'.svn'
						]
					]
			    );
		}

		$this->cache(Cache::instance('file'));
	}

	/**
	 * Tests that ignored files are not removed from file cache
	 *
	 * @return  void
	 */
	public function test_ignore_delete_file()
	{
		$cache = $this->cache();
		$config = Kohana::$config->load('cache')->file;
		$file = $config['cache_dir'].'/file_we_want_to_keep.cache';

		// Lets pollute the cache folder
		file_put_contents($file, 'foobar');

		$this->assertTrue($cache->delete_all());
		$this->assertTrue(file_exists($file));
		$this->assertEquals('foobar', file_get_contents($file));

		unlink($file);
	}

	/**
	 * delete_all() must not fail when the optional ignore_on_delete config
	 * is missing. On PHP 8, in_array() with a NULL haystack is a TypeError.
	 *
	 * @test
	 * @see https://github.com/hospicedev/konine/issues/13
	 */
	public function test_delete_all_without_ignore_on_delete()
	{
		$directory = $this->temp_cache_dir();
		mkdir($directory, 0755);

		$cache = $this->file_cache(['cache_dir' => $directory]);
		$cache->set('foo', 'bar');

		$this->assertTrue($cache->delete_all());
		$this->assertNull($cache->get('foo'));
	}

	/**
	 * A missing cache directory is created on first use, as documented, with
	 * the same 0755 permissions Kohana::init() uses for its cache directory.
	 *
	 * @test
	 * @see https://github.com/hospicedev/konine/issues/13
	 */
	public function test_creates_missing_cache_dir()
	{
		$directory = $this->temp_cache_dir().DIRECTORY_SEPARATOR.'nested'.DIRECTORY_SEPARATOR.'cache';

		$cache = $this->file_cache(['cache_dir' => $directory]);

		$this->assertFalse(is_dir($directory));
		$this->assertTrue($cache->set('foo', 'bar'));
		$this->assertTrue(is_dir($directory));
		$this->assertSame('bar', $cache->get('foo'));

		if (DIRECTORY_SEPARATOR === '/')
		{
			$this->assertSame(0755, fileperms($directory) & 0777);
		}
	}

	/**
	 * An existing cache directory must be used as-is, with its permissions
	 * left untouched.
	 *
	 * @test
	 */
	public function test_existing_cache_dir_permissions_unchanged()
	{
		$directory = $this->temp_cache_dir();
		mkdir($directory, 0700);
		chmod($directory, 0700);

		$cache = $this->file_cache(['cache_dir' => $directory]);

		$this->assertTrue($cache->set('foo', 'bar'));
		$this->assertSame('bar', $cache->get('foo'));

		if (DIRECTORY_SEPARATOR === '/')
		{
			$this->assertSame(0700, fileperms($directory) & 0777);
		}
	}

	/**
	 * If the cache directory cannot be created, a Cache_Exception naming the
	 * directory is thrown rather than a raw PHP warning or ErrorException.
	 *
	 * @test
	 */
	public function test_uncreatable_cache_dir_throws_cache_exception()
	{
		$parent = $this->temp_cache_dir();
		mkdir($parent, 0755);

		// A path below a regular file can never be created, even as root
		$file = $parent.DIRECTORY_SEPARATOR.'not_a_directory';
		file_put_contents($file, 'x');
		$directory = $file.DIRECTORY_SEPARATOR.'cache';

		$cache = $this->file_cache(['cache_dir' => $directory]);

		$this->expectException(Cache_Exception::class);
		$this->expectExceptionMessage($directory);

		$cache->set('foo', 'bar');
	}

	/**
	 * Returns a unique, not yet existing, temporary cache directory path that
	 * is removed again in tearDown().
	 *
	 * @return  string
	 */
	protected function temp_cache_dir()
	{
		$directory = sys_get_temp_dir().DIRECTORY_SEPARATOR.'konine_file_cache_test_'.uniqid('', TRUE);

		$this->temp_dirs[] = $directory;

		return $directory;
	}

	/**
	 * Returns a file cache instance for a one-off config group.
	 *
	 * @param   array  $config  config merged over the file driver defaults
	 * @return  Cache_File
	 */
	protected function file_cache(array $config)
	{
		$group = 'file_test_'.str_replace('.', '_', uniqid('', TRUE));

		Kohana::$config->load('cache')->set($group, $config + [
			'driver'         => 'file',
			'default_expire' => 3600,
		]);

		return Cache::instance($group);
	}

	/**
	 * @var  array  temporary directories created by temp_cache_dir()
	 */
	protected $temp_dirs = [];

	public function tearDown(): void
	{
		foreach ($this->temp_dirs as $directory)
		{
			$this->remove_temp_dir($directory);
		}

		$this->temp_dirs = [];

		parent::tearDown();
	}

	/**
	 * Recursively removes a directory created by temp_cache_dir().
	 *
	 * @param   string  $path
	 * @return  void
	 */
	protected function remove_temp_dir($path)
	{
		// Only ever delete paths this test created
		if (strpos($path, sys_get_temp_dir().DIRECTORY_SEPARATOR.'konine_file_cache_test_') !== 0 OR ! file_exists($path))
			return;

		if (is_dir($path) AND ! is_link($path))
		{
			foreach (array_diff(scandir($path), ['.', '..']) as $entry)
			{
				$this->remove_temp_dir($path.DIRECTORY_SEPARATOR.$entry);
			}

			rmdir($path);
		}
		else
		{
			unlink($path);
		}
	}

	/**
	 * Provider for test_utf8
	 *
	 * @return  array
	 */
	public function provider_utf8()
	{
		return [
			[
				'This is â ütf-8 Ӝ☃ string',
				'This is â ütf-8 Ӝ☃ string'
			],
			[
				'㆓㆕㆙㆛',
				'㆓㆕㆙㆛'
			],
			[
				'அஆஇஈஊ',
				'அஆஇஈஊ'
			]
		];
	}

	/**
	 * Tests the file driver supports utf-8 strings
	 *
	 * @dataProvider provider_utf8
	 *
	 * @return  void
	 */
	public function test_utf8($input, $expected)
	{
		$cache = $this->cache();
		$cache->set('utf8', $input);

		$this->assertSame($expected, $cache->get('utf8'));
	}

	/**
	 * Tests garbage collection.
	 * Tests if non-expired cache files withstand garbage collection
	 *
	 * @test
	 */
	public function test_garbage_collection()
	{
		$cache = $this->cache();
		$cache->set('persistent', 'dummy persistent data', 3);
		$cache->set('volatile', 'dummy volatile data', 1);

		$this->assertTrue($this->is_file('persistent'));
		$this->assertTrue($this->is_file('volatile'));

		// sleep for more than a second
		sleep(2);

		$cache->garbage_collect();

		$this->assertTrue($this->is_file('persistent'));
		$this->assertFalse($this->is_file('volatile'));
	}

	/**
	 * helper method for test_garbage_collection.
	 * Tests if cache file exists given cache id.
	 *
	 * @param string $id cache id
	 * @return boolean TRUE if file exists FALSE otherwise
	 */
	protected function is_file($id)
	{
		$cache = $this->cache();

		$method_sanitize_id = new ReflectionMethod($cache, '_sanitize_id');
		$method_filename = new ReflectionMethod($cache, 'filename');
		$method_resolve_directory = new ReflectionMethod($cache, '_resolve_directory');

		$sanitized_id = $method_sanitize_id->invoke($cache, $id);
		$filename = $method_filename->invoke($cache, $sanitized_id);
		$directory = $method_resolve_directory->invoke($cache, $filename);

		$file = new SplFileInfo($directory.$filename);

		//var_dump($cache->_is_expired($file));
		return $file->isFile();
	}
} // End Kohana_SqliteTest
