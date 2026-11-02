<?php

/**
 * Tests Kohana_ORM against an in-memory SQLite database
 *
 * @package    Kohana/ORM
 * @group      kohana
 * @group      kohana.orm
 * @category   Test
 * @author     Kohana Team
 * @copyright  (c) Kohana Team
 * @license    https://github.com/hospicedev/konine/blob/master/LICENSE.md
 */

class Kohana_ORMDatabaseTest extends Unittest_TestCase
{
	/**
	 * @var Database
	 */
	protected $db;

	public function setUp(): void
	{
		parent::setUp();

		if ( ! extension_loaded('pdo_sqlite'))
		{
			$this->markTestSkipped('The pdo_sqlite extension is not available');
		}

		// No charset: Database_PDO sends SET NAMES, which SQLite does not support
		$this->db = Database::instance('unittest_orm', [
			'type'         => 'PDO',
			'connection'   => [
				'dsn'        => 'sqlite::memory:',
				'persistent' => FALSE,
			],
			'table_prefix' => '',
			'caching'      => FALSE,
		]);

		$statements = [
			'CREATE TABLE ormtest_users (id INTEGER PRIMARY KEY AUTOINCREMENT, username TEXT NOT NULL, email TEXT)',
			'CREATE TABLE ormtest_posts (id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER, title TEXT)',
			'CREATE TABLE ormtest_tags (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT)',
			'CREATE TABLE ormtest_posts_tags (post_id INTEGER, tag_id INTEGER)',
			"INSERT INTO ormtest_users (username, email) VALUES ('alice', 'alice@example.com'), ('bob', 'bob@example.com')",
			"INSERT INTO ormtest_posts (user_id, title) VALUES (1, 'First'), (1, 'Second'), (2, 'Third')",
			"INSERT INTO ormtest_tags (name) VALUES ('php'), ('sqlite'), ('orm')",
			'INSERT INTO ormtest_posts_tags (post_id, tag_id) VALUES (1, 1), (1, 2)',
		];

		foreach ($statements as $sql)
		{
			$this->db->query(NULL, $sql);
		}
	}

	public function tearDown(): void
	{
		if ($this->db)
		{
			$this->db->disconnect();
			$this->db = NULL;
		}

		unset(Database::$instances['unittest_orm']);

		parent::tearDown();
	}

	/**
	 * @test
	 * @covers ORM::__construct
	 * @covers ORM::find
	 * @covers ORM::_load_values
	 * @covers ORM::loaded
	 * @covers ORM::pk
	 */
	public function test_loads_record_by_primary_key()
	{
		$user = ORM::factory('ORMTest_User', 2);

		$this->assertTrue($user->loaded());
		$this->assertEquals(2, $user->pk());
		$this->assertSame('bob', $user->username);
		$this->assertSame('bob@example.com', $user->email);
	}

	/**
	 * @test
	 * @covers ORM::__construct
	 * @covers ORM::find
	 * @covers ORM::clear
	 * @covers ORM::loaded
	 */
	public function test_missing_primary_key_is_not_loaded()
	{
		$user = ORM::factory('ORMTest_User', 99);

		$this->assertFalse($user->loaded());
		$this->assertNull($user->pk());
		$this->assertNull($user->username);
	}

	/**
	 * @test
	 * @covers ORM::where
	 * @covers ORM::find
	 * @covers ORM::_load_result
	 * @covers ORM::_load_values
	 */
	public function test_find_loads_first_matching_row()
	{
		$user = ORM::factory('ORMTest_User')
			->where('username', '=', 'bob')
			->find();

		$this->assertTrue($user->loaded());
		$this->assertEquals(2, $user->pk());
	}

	/**
	 * @test
	 * @covers ORM::where
	 * @covers ORM::order_by
	 * @covers ORM::find_all
	 * @covers ORM::_load_result
	 * @covers ORM::__construct
	 */
	public function test_find_all_returns_models_for_matching_rows()
	{
		$posts = ORM::factory('ORMTest_Post')
			->where('user_id', '=', 1)
			->order_by('id', 'DESC')
			->find_all();

		$this->assertCount(2, $posts);
		$this->assertContainsOnlyInstancesOf('Model_ORMTest_Post', $posts);
		$this->assertSame(['Second', 'First'], $posts->as_array(NULL, 'title'));
		$this->assertTrue($posts[0]->loaded());
	}

	/**
	 * @test
	 * @covers ORM::set
	 * @covers ORM::save
	 * @covers ORM::create
	 * @covers ORM::check
	 * @covers ORM::saved
	 * @covers ORM::changed
	 */
	public function test_save_creates_record_and_sets_primary_key()
	{
		$user = ORM::factory('ORMTest_User');
		$user->username = 'carol';
		$user->save();

		$this->assertTrue($user->loaded());
		$this->assertTrue($user->saved());
		$this->assertEquals(3, $user->pk());
		$this->assertSame([], $user->changed());
		$this->assertSame('carol', ORM::factory('ORMTest_User', $user->pk())->username);
	}

	/**
	 * values() is the mass-assignment guard for request data, so keys outside
	 * $expected must be ignored.
	 *
	 * @test
	 * @covers ORM::values
	 * @covers ORM::set
	 * @covers ORM::changed
	 * @covers ORM::save
	 * @covers ORM::create
	 */
	public function test_values_ignores_keys_not_expected()
	{
		$user = ORM::factory('ORMTest_User')
			->values(['username' => 'carol', 'email' => 'carol@example.com', 'id' => 99], ['username']);

		$this->assertSame(['username' => 'username'], $user->changed());
		$this->assertNull($user->email);

		$user->save();

		$this->assertEquals(3, $user->pk());
		$this->assertFalse(ORM::factory('ORMTest_User', 99)->loaded());
	}

	/**
	 * Without $expected, values() must still drop the primary key, so that
	 * ->values($_POST) cannot choose the id of a new record.
	 *
	 * @test
	 * @covers ORM::values
	 * @covers ORM::set
	 * @covers ORM::changed
	 * @covers ORM::save
	 * @covers ORM::create
	 */
	public function test_values_without_expected_skips_primary_key_on_create()
	{
		$user = ORM::factory('ORMTest_User')
			->values(['id' => 99, 'username' => 'carol', 'email' => 'carol@example.com']);

		$this->assertSame(['username' => 'username', 'email' => 'email'], $user->changed());

		$user->save();

		$this->assertEquals(3, $user->pk());
		$this->assertFalse(ORM::factory('ORMTest_User', 99)->loaded());
	}

	/**
	 * Without $expected, values() must not let ->values($_POST) move an
	 * update onto another record.
	 *
	 * @test
	 * @covers ORM::values
	 * @covers ORM::set
	 * @covers ORM::save
	 * @covers ORM::update
	 */
	public function test_values_without_expected_skips_primary_key_on_update()
	{
		$user = ORM::factory('ORMTest_User', 1)
			->values(['id' => 2, 'email' => 'new@example.com'])
			->save();

		$this->assertEquals(1, $user->pk());
		$this->assertSame('new@example.com', ORM::factory('ORMTest_User', 1)->email);
		$this->assertSame('bob@example.com', ORM::factory('ORMTest_User', 2)->email);
	}

	/**
	 * The email is changed behind the model's back, so it only survives if
	 * update() writes nothing but the changed column.
	 *
	 * @test
	 * @covers ORM::set
	 * @covers ORM::changed
	 * @covers ORM::save
	 * @covers ORM::update
	 */
	public function test_update_writes_only_changed_columns()
	{
		$user = ORM::factory('ORMTest_User', 1);

		$user->email = 'alice@example.com';
		$user->username = 'alicia';

		$this->assertSame(['username' => 'username'], $user->changed());

		DB::update('ormtest_users')
			->set(['email' => 'changed@example.com'])
			->where('id', '=', 1)
			->execute($this->db);

		$user->save();

		$this->assertSame([], $user->changed());

		$row = DB::select('username', 'email')
			->from('ormtest_users')
			->where('id', '=', 1)
			->execute($this->db)
			->current();

		$this->assertSame(['username' => 'alicia', 'email' => 'changed@example.com'], $row);
	}

	/**
	 * @test
	 * @covers ORM::delete
	 * @covers ORM::clear
	 * @covers ORM::count_all
	 */
	public function test_delete_removes_record_and_clears_model()
	{
		$user = ORM::factory('ORMTest_User', 1);
		$user->delete();

		$this->assertFalse($user->loaded());
		$this->assertNull($user->pk());
		$this->assertFalse(ORM::factory('ORMTest_User', 1)->loaded());
		$this->assertSame(1, ORM::factory('ORMTest_User')->count_all());
	}

	/**
	 * @test
	 * @covers ORM::get
	 * @covers ORM::_related
	 */
	public function test_belongs_to_loads_parent()
	{
		$post = ORM::factory('ORMTest_Post', 3);

		$this->assertInstanceOf('Model_ORMTest_User', $post->author);
		$this->assertTrue($post->author->loaded());
		$this->assertSame('bob', $post->author->username);
	}

	/**
	 * @test
	 * @covers ORM::get
	 * @covers ORM::find_all
	 * @covers ORM::count_all
	 */
	public function test_has_many_finds_children()
	{
		$user = ORM::factory('ORMTest_User', 1);

		$titles = $user->posts->order_by('id')->find_all()->as_array(NULL, 'title');

		$this->assertSame(['First', 'Second'], $titles);
		$this->assertSame(1, ORM::factory('ORMTest_User', 2)->posts->count_all());
	}

	/**
	 * @test
	 * @covers ORM::get
	 * @covers ORM::join
	 * @covers ORM::on
	 * @covers ORM::find_all
	 */
	public function test_has_many_through_finds_related()
	{
		$post = ORM::factory('ORMTest_Post', 1);

		$names = $post->tags->order_by('id')->find_all()->as_array(NULL, 'name');

		$this->assertSame(['php', 'sqlite'], $names);
	}

	/**
	 * @test
	 * @covers ORM::add
	 * @covers ORM::remove
	 * @covers ORM::has
	 * @covers ORM::count_relations
	 */
	public function test_add_and_remove_has_many_through()
	{
		$post = ORM::factory('ORMTest_Post', 2);
		$tag = ORM::factory('ORMTest_Tag', 3);

		$this->assertFalse($post->has('tags'));

		$post->add('tags', $tag);
		$post->add('tags', [1, 2]);

		$this->assertTrue($post->has('tags', $tag));
		$this->assertTrue($post->has('tags', [1, 2, 3]));

		$post->remove('tags', 2);

		$this->assertFalse($post->has('tags', 2));
		$this->assertFalse($post->has('tags', [1, 2, 3]));
		$this->assertTrue($post->has('tags', [1, 3]));

		$post->remove('tags');

		$this->assertFalse($post->has('tags'));
	}

	/**
	 * @test
	 * @covers ORM::save
	 * @covers ORM::create
	 * @covers ORM::check
	 * @covers ORM::_validation
	 * @covers ORM_Validation_Exception::errors
	 */
	public function test_failed_rules_throw_validation_exception()
	{
		$user = ORM::factory('ORMTest_User');
		$user->email = 'nobody@example.com';

		try
		{
			$user->save();

			$this->fail('Expected ORM_Validation_Exception');
		}
		catch (ORM_Validation_Exception $e)
		{
			$errors = $e->errors();

			$this->assertSame(['username'], array_keys($errors));
			$this->assertSame('not_empty', $errors['username'][0]);
		}

		$this->assertFalse($user->loaded());
		$this->assertSame(2, ORM::factory('ORMTest_User')->count_all());
	}

	/**
	 * ORM reloads the record on wakeup by default, so a row changed while the
	 * model was serialized (e.g. stored in a session) must come back with the
	 * current database values rather than the serialized ones.
	 *
	 * @test
	 * @covers ORM::__serialize
	 * @covers ORM::__unserialize
	 * @covers ORM::__wakeup
	 * @covers ORM::reload
	 */
	public function test_unserialize_reloads_current_row()
	{
		$serialized = serialize(ORM::factory('ORMTest_User', 2));

		DB::update('ormtest_users')
			->set(['email' => 'changed@example.com'])
			->where('id', '=', 2)
			->execute($this->db);

		$user = unserialize($serialized);

		$this->assertInstanceOf('Model_ORMTest_User', $user);
		$this->assertTrue($user->loaded());
		$this->assertEquals(2, $user->pk());
		$this->assertSame('bob', $user->username);
		$this->assertSame('changed@example.com', $user->email);
	}
}

/**
 * Base for models stored in the unittest_orm SQLite database. Database_PDO
 * does not implement list_columns(), so the models declare their columns.
 */
abstract class ORM_SQLite_Example extends ORM
{
	protected $_db_group = 'unittest_orm';

	/**
	 * ORM caches the connection per model in a static, so pick up the
	 * connection of the current test instead.
	 */
	protected function _initialize()
	{
		$this->_db = Database::instance($this->_db_group);

		parent::_initialize();
	}
}

class Model_ORMTest_User extends ORM_SQLite_Example
{
	protected $_table_columns = [
		'id'       => ['type' => 'int'],
		'username' => ['type' => 'string'],
		'email'    => ['type' => 'string'],
	];

	protected $_has_many = [
		'posts' => ['model' => 'ORMTest_Post', 'foreign_key' => 'user_id'],
	];

	public function rules()
	{
		return [
			'username' => [
				['not_empty'],
			],
		];
	}
}

class Model_ORMTest_Post extends ORM_SQLite_Example
{
	protected $_table_columns = [
		'id'      => ['type' => 'int'],
		'user_id' => ['type' => 'int'],
		'title'   => ['type' => 'string'],
	];

	protected $_belongs_to = [
		'author' => ['model' => 'ORMTest_User', 'foreign_key' => 'user_id'],
	];

	protected $_has_many = [
		'tags' => [
			'model'       => 'ORMTest_Tag',
			'through'     => 'ormtest_posts_tags',
			'foreign_key' => 'post_id',
			'far_key'     => 'tag_id',
		],
	];
}

class Model_ORMTest_Tag extends ORM_SQLite_Example
{
	protected $_table_columns = [
		'id'   => ['type' => 'int'],
		'name' => ['type' => 'string'],
	];
}
