<?php

/**
 * Test case for Kohana_Auth with the File driver
 *
 * @package    Kohana/Auth
 * @group      kohana
 * @group      kohana.auth
 * @category   Test
 * @author     Kohana Team
 * @copyright  (c) Kohana Team
 * @license    https://github.com/hospicedev/konine/blob/master/LICENSE.md
 */

class Kohana_AuthTest extends Unittest_TestCase
{
	/**
	 * Auth config used to build the driver in these tests
	 * @var array
	 */
	protected $config = [
		'hash_method' => 'sha256',
		'hash_key' => 'test-hash-key',
		'session_type' => 'native',
		'session_key' => 'auth_test_user',
	];

	public function setUp(): void
	{
		parent::setUp();

		$this->config['users'] = [
			'test_user' => hash_hmac('sha256', 'test password', 'test-hash-key'),
		];

		// The native session is shared by all tests, start each one logged out
		Session::instance('native')->delete($this->config['session_key']);
	}

	public function tearDown(): void
	{
		Session::instance('native')->delete($this->config['session_key']);

		parent::tearDown();
	}

	/**
	 * @test
	 * @covers Auth::__construct
	 * @covers Auth::hash
	 * @covers Auth_File::__construct
	 */
	public function test_hash_uses_configured_method_and_key()
	{
		$auth = new Auth_File($this->config);

		$this->assertSame(hash_hmac('sha256', 'test password', 'test-hash-key'), $auth->hash('test password'));
	}

	/**
	 * @test
	 * @covers Auth::hash
	 */
	public function test_hash_without_key_throws_exception()
	{
		$auth = new Auth_File(['hash_key' => NULL] + $this->config);

		$this->expectException('Kohana_Exception');

		$auth->hash('test password');
	}

	/**
	 * @test
	 * @covers Auth::login
	 * @covers Auth::logged_in
	 * @covers Auth::get_user
	 * @covers Auth::complete_login
	 * @covers Auth_File::_login
	 */
	public function test_login_with_correct_password()
	{
		$auth = new Auth_File($this->config);

		$this->assertTrue($auth->login('test_user', 'test password'));
		$this->assertTrue($auth->logged_in());
		$this->assertSame('test_user', $auth->get_user());
	}

	/**
	 * Provides test data for test_login_with_wrong_credentials_fails()
	 *
	 * @return array
	 */
	public function provider_login_with_wrong_credentials_fails()
	{
		return [
			// [username, password]
			['test_user', 'wrong password'],
			['test_user', ''],
			['unknown_user', 'test password'],
			['test_user', hash_hmac('sha256', 'test password', 'test-hash-key')],
		];
	}

	/**
	 * @test
	 * @dataProvider provider_login_with_wrong_credentials_fails
	 * @covers Auth::login
	 * @covers Auth::logged_in
	 * @covers Auth_File::_login
	 */
	public function test_login_with_wrong_credentials_fails($username, $password)
	{
		$auth = new Auth_File($this->config);

		$this->assertFalse($auth->login($username, $password));
		$this->assertFalse($auth->logged_in());
	}

	/**
	 * @test
	 * @covers Auth::logout
	 * @covers Auth::logged_in
	 * @covers Auth::get_user
	 */
	public function test_logout()
	{
		$auth = new Auth_File($this->config);
		$auth->login('test_user', 'test password');

		$this->assertTrue($auth->logout());
		$this->assertFalse($auth->logged_in());
		$this->assertNull($auth->get_user());
	}

	/**
	 * @test
	 * @covers Auth_File::password
	 */
	public function test_password_returns_stored_hash()
	{
		$auth = new Auth_File($this->config);

		$this->assertSame($this->config['users']['test_user'], $auth->password('test_user'));
		$this->assertFalse($auth->password('unknown_user'));
	}
}
