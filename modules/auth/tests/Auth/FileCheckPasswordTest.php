<?php

/**
 * Tests Auth_File::check_password()
 *
 * @package    Kohana/Auth
 * @group      kohana
 * @group      kohana.auth
 * @category   Test
 * @author     Kohana Team
 * @copyright  (c) Kohana Team
 * @license    https://github.com/hospicedev/konine/blob/master/LICENSE.md
 */
class Kohana_Auth_FileCheckPasswordTest extends Unittest_TestCase
{
	const HASH_METHOD = 'sha256';
	const HASH_KEY = 'test-hash-key';
	const SESSION_KEY = 'auth_file_check_password_test_user';
	const USERNAME = 'test_user';
	const PASSWORD = 'test password';

	/**
	 * Auth config used to build the driver in these tests
	 * @var array
	 */
	protected $config;

	public function setUp(): void
	{
		parent::setUp();

		$this->config = [
			'hash_method' => self::HASH_METHOD,
			'hash_key' => self::HASH_KEY,
			'session_type' => 'native',
			'session_key' => self::SESSION_KEY,
			'users' => [
				self::USERNAME => $this->stored_hash(),
			],
		];

		// The native session is shared by all tests, start each one logged out
		Session::instance('native')->delete(self::SESSION_KEY);
	}

	public function tearDown(): void
	{
		Session::instance('native')->delete(self::SESSION_KEY);

		parent::tearDown();
	}

	/**
	 * The hash stored in the config for the test user
	 *
	 * @return string
	 */
	protected function stored_hash()
	{
		return hash_hmac(self::HASH_METHOD, self::PASSWORD, self::HASH_KEY);
	}

	/**
	 * Builds the driver with the test user logged in
	 *
	 * @return Auth_File
	 */
	protected function logged_in_auth()
	{
		$auth = new Auth_File($this->config);
		$auth->force_login(self::USERNAME);

		return $auth;
	}

	/**
	 * @test
	 * @covers Auth_File::check_password
	 */
	public function test_correct_password_is_accepted()
	{
		$this->assertTrue($this->logged_in_auth()->check_password(self::PASSWORD));
	}

	/**
	 * Provides test data for test_incorrect_password_is_rejected()
	 *
	 * @return array
	 */
	public static function provider_incorrect_password_is_rejected()
	{
		return [
			'wrong password' => ['wrong password'],
			'empty password' => [''],
			'stored hash' => [hash_hmac(self::HASH_METHOD, self::PASSWORD, self::HASH_KEY)],
		];
	}

	/**
	 * @test
	 * @dataProvider provider_incorrect_password_is_rejected
	 * @covers Auth_File::check_password
	 */
	public function test_incorrect_password_is_rejected($password)
	{
		$this->assertFalse($this->logged_in_auth()->check_password($password));
	}

	/**
	 * @test
	 * @covers Auth_File::check_password
	 */
	public function test_returns_false_when_no_user_is_logged_in()
	{
		$auth = new Auth_File($this->config);

		$this->assertFalse($auth->check_password(self::PASSWORD));
	}

	/**
	 * The stored password lookup returns FALSE when no user is found, which
	 * previously let a boolean FALSE input pass the check.
	 *
	 * @test
	 * @covers Auth_File::check_password
	 */
	public function test_boolean_false_is_rejected_when_no_user_is_logged_in()
	{
		$auth = new Auth_File($this->config);

		$this->assertFalse($auth->check_password(FALSE));
	}

	/**
	 * @test
	 * @covers Auth_File::check_password
	 */
	public function test_returns_false_when_logged_in_user_is_not_configured()
	{
		$auth = new Auth_File($this->config);
		$auth->force_login('unknown_user');

		$this->assertFalse($auth->check_password(self::PASSWORD));
	}

}
