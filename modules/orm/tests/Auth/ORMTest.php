<?php

/**
 * Tests the Auth_ORM and Auth_Bcrypt drivers against an in-memory SQLite
 * database
 *
 * @package    Kohana/Auth
 * @group      kohana
 * @group      kohana.auth
 * @group      kohana.orm
 * @category   Test
 * @author     Kohana Team
 * @copyright  (c) Kohana Team
 * @license    https://github.com/hospicedev/konine/blob/master/LICENSE.md
 */

class Kohana_Auth_ORMTest extends Unittest_TestCase
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

		$this->db = Database::instance('unittest_auth', [
			'type'         => 'PDO',
			'connection'   => [
				'dsn'        => 'sqlite::memory:',
				'persistent' => FALSE,
			],
			'table_prefix' => '',
			'caching'      => FALSE,
		]);

		// Model_User and Model_Role use the default database group, and ORM
		// caches each model's connection and columns. Database_PDO cannot
		// list columns, so they are provided here. Model_Auth_User::filters()
		// calls Auth::instance(), which must not start a native session.
		$this->setEnvironment([
			'Database::$default'         => 'unittest_auth',
			'Kohana_ORM::$_init_cache'   => [],
			'Kohana_ORM::$_column_cache' => [
				'user' => array_fill_keys(['id', 'email', 'username', 'password', 'logins', 'last_login'], ['type' => 'string']),
				'role' => array_fill_keys(['id', 'name', 'description'], ['type' => 'string']),
			],
			'Kohana_Auth::$_instance'    => NULL,
			'Session::$instances'        => ['unittest_auth' => new Session_Auth_Example] + Session::$instances,
			'auth.driver'                => 'ORM',
			'auth.hash_method'           => 'sha256',
			'auth.hash_key'              => 'unittest-key',
			'auth.cost'                  => 10,
			'auth.session_type'          => 'unittest_auth',
			'auth.session_key'           => 'auth_user',
		]);

		$statements = [
			'CREATE TABLE roles (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT NOT NULL UNIQUE, description TEXT NOT NULL)',
			'CREATE TABLE roles_users (user_id INTEGER NOT NULL, role_id INTEGER NOT NULL, PRIMARY KEY (user_id, role_id))',
			"CREATE TABLE users (id INTEGER PRIMARY KEY AUTOINCREMENT, email TEXT NOT NULL UNIQUE, username TEXT NOT NULL DEFAULT '' UNIQUE, password TEXT NOT NULL, logins INTEGER NOT NULL DEFAULT 0, last_login INTEGER)",
			'CREATE TABLE user_tokens (id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER NOT NULL, user_agent TEXT NOT NULL, token TEXT NOT NULL UNIQUE, created INTEGER NOT NULL, expires INTEGER NOT NULL)',
			"INSERT INTO roles (id, name, description) VALUES (1, 'login', 'Login privileges'), (2, 'admin', 'Administrative user')",
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

		unset(Database::$instances['unittest_auth']);

		parent::tearDown();
	}

	/**
	 * Returns Auth::instance() for the given driver, so the driver under test
	 * is also the one Model_Auth_User::filters() hashes passwords with.
	 *
	 * @param  string $driver
	 * @return Auth
	 */
	protected function auth($driver)
	{
		$this->setEnvironment(['auth.driver' => $driver]);

		return Auth::instance();
	}

	/**
	 * Inserts a user with the given password hash and role ids.
	 *
	 * @param  string $username
	 * @param  string $password Stored password hash
	 * @param  array  $roles    Role ids
	 * @return void
	 */
	protected function insert_user($username, $password, array $roles)
	{
		list($id) = DB::insert('users', ['username', 'email', 'password'])
			->values([$username, $username.'@example.com', $password])
			->execute($this->db);

		foreach ($roles as $role)
		{
			DB::insert('roles_users', ['user_id', 'role_id'])
				->values([$id, $role])
				->execute($this->db);
		}
	}

	/**
	 * @test
	 * @covers Auth::instance
	 * @covers Auth::login
	 * @covers Auth::complete_login
	 * @covers Auth_ORM::_login
	 * @covers Auth_ORM::complete_login
	 * @covers Auth_ORM::logged_in
	 * @covers Auth_ORM::get_user
	 * @covers Model_Auth_User::unique_key
	 * @covers Model_Auth_User::complete_login
	 */
	public function test_orm_login_succeeds_for_user_with_login_role()
	{
		$auth = $this->auth('ORM');

		$this->insert_user('alice', $auth->hash('password'), [1]);

		$this->assertFalse($auth->logged_in());
		$this->assertTrue($auth->login('alice', 'password'));

		$this->assertTrue($auth->logged_in());
		$this->assertTrue($auth->logged_in('login'));
		$this->assertFalse($auth->logged_in('admin'));
		$this->assertSame('alice', $auth->get_user()->username);

		$logins = DB::select('logins')
			->from('users')
			->where('username', '=', 'alice')
			->execute($this->db)
			->get('logins');

		$this->assertEquals(1, $logins);
	}

	/**
	 * @test
	 * @covers Auth::login
	 * @covers Auth_ORM::_login
	 * @covers Auth_ORM::logged_in
	 */
	public function test_orm_login_fails_for_wrong_password_or_missing_login_role()
	{
		$auth = $this->auth('ORM');

		$this->insert_user('alice', $auth->hash('password'), [1]);
		$this->insert_user('bob', $auth->hash('password'), [2]);

		$this->assertFalse($auth->login('alice', 'wrong-password'));
		$this->assertFalse($auth->login('bob', 'password'));
		$this->assertFalse($auth->logged_in());
	}

	/**
	 * @test
	 * @covers Auth::login
	 * @covers Auth::logout
	 * @covers Auth_ORM::logout
	 * @covers Auth_ORM::logged_in
	 * @covers Auth_ORM::get_user
	 */
	public function test_orm_logout_clears_logged_in_user()
	{
		$auth = $this->auth('ORM');

		$this->insert_user('alice', $auth->hash('password'), [1]);

		$this->assertTrue($auth->login('alice', 'password'));
		$this->assertTrue($auth->logged_in());

		$this->assertTrue($auth->logout());
		$this->assertFalse($auth->logged_in());
		$this->assertNull($auth->get_user());
	}

	/**
	 * @test
	 * @covers Auth_Bcrypt::__construct
	 * @covers Auth_Bcrypt::hash
	 */
	public function test_bcrypt_hash_verifies_with_password_verify()
	{
		$hash = $this->auth('Bcrypt')->hash('password');

		$this->assertTrue(password_verify('password', $hash));
		$this->assertFalse(password_verify('wrong-password', $hash));
		$this->assertSame(10, password_get_info($hash)['options']['cost']);
	}

	/**
	 * @test
	 * @covers Auth::login
	 * @covers Auth::logged_in
	 * @covers Auth::complete_login
	 * @covers Auth_Bcrypt::_login
	 * @covers Auth_Bcrypt::get_user
	 * @covers Model_Auth_User::complete_login
	 */
	public function test_bcrypt_login_checks_password()
	{
		$auth = $this->auth('Bcrypt');

		$this->insert_user('alice', $auth->hash('password'), [1]);

		$this->assertFalse($auth->login('alice', 'wrong-password'));
		$this->assertFalse($auth->logged_in());

		$this->assertTrue($auth->login('alice', 'password'));
		$this->assertTrue($auth->logged_in());
		$this->assertSame('alice', $auth->get_user()->username);
	}

	/**
	 * @return array
	 */
	public function provider_create_user_hashes_password()
	{
		return [
			['ORM', '/^[0-9a-f]{64}$/'],
			['Bcrypt', '/^\$2y\$10\$/'],
		];
	}

	/**
	 * Model_Auth_User::filters() hashes the password through Auth::instance(),
	 * so a user created through the model must be able to log in with the
	 * same driver.
	 *
	 * @test
	 * @dataProvider provider_create_user_hashes_password
	 * @covers Model_Auth_User::create_user
	 * @covers Model_Auth_User::filters
	 * @covers Model_Auth_User::get_password_validation
	 * @covers Auth::hash
	 * @covers Auth::login
	 * @covers Auth_ORM::_login
	 * @covers Auth_Bcrypt::hash
	 * @covers Auth_Bcrypt::_login
	 * @param string $driver      Auth driver
	 * @param string $hash_format Expected format of the stored password
	 */
	public function test_create_user_stores_password_hash_that_logs_in($driver, $hash_format)
	{
		$auth = $this->auth($driver);

		$user = ORM::factory('User')->create_user([
			'username'         => 'carol',
			'email'            => 'carol@example.com',
			'password'         => 'test password',
			'password_confirm' => 'test password',
		], ['username', 'email', 'password']);

		$user->add('roles', ORM::factory('Role', ['name' => 'login']));

		$stored = DB::select('password')
			->from('users')
			->where('id', '=', $user->pk())
			->execute($this->db)
			->get('password');

		$this->assertNotSame('test password', $stored);
		$this->assertMatchesRegularExpression($hash_format, $stored);
		$this->assertTrue($auth->login('carol', 'test password'));
	}

	/**
	 * @return array
	 */
	public function provider_drivers()
	{
		return [
			['ORM'],
			['Bcrypt'],
		];
	}

	/**
	 * @test
	 * @dataProvider provider_drivers
	 * @covers Auth_ORM::check_password
	 * @covers Auth_Bcrypt::check_password
	 * @param string $driver Auth driver
	 */
	public function test_check_password_compares_with_logged_in_user($driver)
	{
		$auth = $this->auth($driver);

		$this->insert_user('alice', $auth->hash('password'), [1]);

		$this->assertFalse($auth->check_password('password'));
		$this->assertTrue($auth->login('alice', 'password'));
		$this->assertTrue($auth->check_password('password'));
		$this->assertFalse($auth->check_password('wrong-password'));
	}

	/**
	 * @test
	 * @dataProvider provider_drivers
	 * @covers Auth::login
	 * @covers Auth_ORM::_login
	 * @covers Auth_Bcrypt::_login
	 * @covers Model_Auth_User::unique_key
	 * @param string $driver Auth driver
	 */
	public function test_login_by_email_and_failed_logins($driver)
	{
		$auth = $this->auth($driver);

		$this->insert_user('alice', $auth->hash('password'), [1]);

		$this->assertFalse($auth->login('nobody', 'password'));
		$this->assertFalse($auth->login('alice', ''));
		$this->assertFalse($auth->logged_in());

		$this->assertTrue($auth->login('alice@example.com', 'password'));
		$this->assertSame('alice', $auth->get_user()->username);
	}

	/**
	 * force_login() must refuse unknown usernames instead of storing an
	 * empty user in the session.
	 *
	 * @test
	 * @dataProvider provider_drivers
	 * @covers Auth_ORM::force_login
	 * @covers Auth_ORM::complete_login
	 * @covers Auth_Bcrypt::force_login
	 * @covers Auth::complete_login
	 * @covers Model_Auth_User::unique_key
	 * @param string $driver Auth driver
	 */
	public function test_force_login_requires_existing_user($driver)
	{
		$auth = $this->auth($driver);

		$this->insert_user('alice', $auth->hash('password'), [1]);

		try
		{
			$auth->force_login('nobody');

			$this->fail('Expected Kohana_Exception');
		}
		catch (Kohana_Exception $e)
		{
			$this->assertFalse($auth->logged_in());
		}

		$auth->force_login('alice');

		$this->assertTrue($auth->logged_in());
		$this->assertSame('alice', $auth->get_user()->username);
	}
}

/**
 * Session kept in memory, so the tests never start a native PHP session.
 */
class Session_Auth_Example extends Session
{
	protected function _read($id = NULL)
	{
		return NULL;
	}

	protected function _regenerate()
	{
		return 'unittest';
	}

	protected function _write()
	{
		return TRUE;
	}

	protected function _destroy()
	{
		return TRUE;
	}

	protected function _restart()
	{
		return TRUE;
	}
}
