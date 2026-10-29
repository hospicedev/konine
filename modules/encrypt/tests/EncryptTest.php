<?php

/**
 * Test case for Kohana_Encrypt with the OpenSSL engine
 *
 * @package    Kohana/Encrypt
 * @group      kohana
 * @group      kohana.encrypt
 * @category   Test
 * @author     Kohana Team
 * @copyright  (c) Kohana Team
 * @license    https://github.com/hospicedev/konine/blob/master/LICENSE.md
 */

class Kohana_EncryptTest extends Unittest_TestCase
{
	/**
	 * 32 byte key for the default AES-256-CBC cipher
	 * @var string
	 */
	protected $key = 'test-key-0123456789-test-key-012';

	/**
	 * Provides test data for test_decode_returns_encoded_text()
	 *
	 * @return array
	 */
	public function provider_decode_returns_encoded_text()
	{
		return [
			// [cipher, key, text]
			['AES-256-CBC', 'test-key-0123456789-test-key-012', 'test text'],
			['AES-256-CBC', 'test-key-0123456789-test-key-012', ''],
			['AES-256-CBC', 'test-key-0123456789-test-key-012', "binary \x00\xff data and ünïcödé"],
			['AES-128-CBC', 'test-key-0123456', str_repeat('more than one block ', 10)],
		];
	}

	/**
	 * @test
	 * @dataProvider provider_decode_returns_encoded_text
	 * @covers Encrypt::__construct
	 * @covers Encrypt::encode
	 * @covers Encrypt::decode
	 * @covers Encrypt::_create_iv
	 * @covers Encrypt_Engine::__construct
	 * @covers Encrypt_Engine_Openssl::__construct
	 * @covers Encrypt_Engine_Openssl::encrypt
	 * @covers Encrypt_Engine_Openssl::decrypt
	 * @covers Encrypt_Engine_Openssl::valid_payload
	 * @covers Encrypt_Engine_Openssl::valid_mac
	 * @covers Encrypt_Engine_Openssl::hash
	 * @covers Encrypt_Engine_Openssl::create_iv
	 */
	public function test_decode_returns_encoded_text($cipher, $key, $text)
	{
		$encrypt = new Encrypt(['key' => $key, 'cipher' => $cipher]);

		$encoded = $encrypt->encode($text);

		$this->assertNotSame($text, $encoded);
		$this->assertSame($text, $encrypt->decode($encoded));
	}

	/**
	 * @test
	 * @covers Encrypt::__construct
	 * @covers Encrypt::encode
	 * @covers Encrypt::decode
	 * @covers Encrypt_Engine::__construct
	 * @covers Encrypt_Engine_Openssl::__construct
	 */
	public function test_string_key_constructor_uses_default_cipher()
	{
		$encrypt = new Encrypt($this->key);

		$this->assertSame('test text', $encrypt->decode($encrypt->encode('test text')));
	}

	/**
	 * Each encode uses a random IV, so the same text must not produce the
	 * same output twice.
	 *
	 * @test
	 * @covers Encrypt::encode
	 * @covers Encrypt_Engine_Openssl::create_iv
	 */
	public function test_encode_is_not_deterministic()
	{
		$encrypt = new Encrypt(['key' => $this->key]);

		$this->assertNotSame($encrypt->encode('test text'), $encrypt->encode('test text'));
	}

	/**
	 * Data encrypted by earlier releases must stay decodable, so the payload
	 * format and MAC derivation must not change.
	 *
	 * @test
	 * @covers Encrypt::decode
	 * @covers Encrypt_Engine_Openssl::decrypt
	 * @covers Encrypt_Engine_Openssl::valid_payload
	 * @covers Encrypt_Engine_Openssl::valid_mac
	 * @covers Encrypt_Engine_Openssl::hash
	 */
	public function test_decode_pinned_payload()
	{
		$encrypt = new Encrypt(['key' => $this->key]);

		$encoded = 'eyJpdiI6IjVXSXowOFJuZXF3VmtKS0Y1Rk1qcVE9PSIsInZhbHVlIjoidVFtNTl2V3FSajVIWU1aM2JKTUIxQT09IiwibWFj'
			.'IjoiZTVjOWZiNjk2MWM0ZDlmYTIwMTZmNjNkMTMyN2QwZTQ2ZDA0NWM5YWQ5ZTIzNWMyOGE2MTc0ODIwNGZlOGFjYyJ9';

		$this->assertSame('test text', $encrypt->decode($encoded));
	}

	/**
	 * @test
	 * @covers Encrypt::decode
	 * @covers Encrypt_Engine_Openssl::decrypt
	 * @covers Encrypt_Engine_Openssl::valid_mac
	 */
	public function test_decode_with_different_key_returns_false()
	{
		$encoded = (new Encrypt(['key' => $this->key]))->encode('test text');

		$encrypt = new Encrypt(['key' => 'other-key-0123456789-other-key-0']);

		$this->assertFalse($encrypt->decode($encoded));
	}

	/**
	 * @test
	 * @covers Encrypt::decode
	 * @covers Encrypt_Engine_Openssl::decrypt
	 * @covers Encrypt_Engine_Openssl::valid_mac
	 */
	public function test_decode_with_tampered_mac_returns_false()
	{
		$encrypt = new Encrypt(['key' => $this->key]);

		$payload = json_decode(base64_decode($encrypt->encode('test text')), TRUE);
		$payload['mac'] = strrev($payload['mac']);

		$this->assertFalse($encrypt->decode(base64_encode(json_encode($payload))));
	}

	/**
	 * Flipping a bit in the IV flips the same bit in the first plaintext
	 * block, so without the MAC check this would decode to "uest text".
	 *
	 * @test
	 * @covers Encrypt::decode
	 * @covers Encrypt_Engine_Openssl::decrypt
	 * @covers Encrypt_Engine_Openssl::valid_mac
	 */
	public function test_decode_with_tampered_iv_returns_false()
	{
		$encrypt = new Encrypt(['key' => $this->key]);

		$payload = json_decode(base64_decode($encrypt->encode('test text')), TRUE);

		$iv = base64_decode($payload['iv']);
		$iv[0] = chr(ord($iv[0]) ^ 1);
		$payload['iv'] = base64_encode($iv);

		$this->assertFalse($encrypt->decode(base64_encode(json_encode($payload))));
	}

	/**
	 * @test
	 * @covers Encrypt::decode
	 * @covers Encrypt_Engine_Openssl::decrypt
	 * @covers Encrypt_Engine_Openssl::valid_payload
	 */
	public function test_decode_tampered_data_returns_false()
	{
		$encrypt = new Encrypt(['key' => $this->key]);

		$payload = json_decode(base64_decode($encrypt->encode('test text')), TRUE);

		// Swap in the ciphertext of another text
		$other = json_decode(base64_decode($encrypt->encode('evil text')), TRUE);
		$payload['value'] = $other['value'];

		$this->assertFalse($encrypt->decode(base64_encode(json_encode($payload))));
		$this->assertFalse($encrypt->decode('not encoded data'));
	}

	/**
	 * Provides test data for test_invalid_key_or_cipher_throws_exception()
	 *
	 * @return array
	 */
	public function provider_invalid_key_or_cipher_throws_exception()
	{
		return [
			// [key, cipher]
			['test-key-0123456', 'AES-256-CBC'],
			['test-key-0123456789-test-key-012', 'AES-128-CBC'],
			['test-key-0123456789-test-key-012', 'AES-256-GCM'],
		];
	}

	/**
	 * @test
	 * @dataProvider provider_invalid_key_or_cipher_throws_exception
	 * @covers Encrypt::__construct
	 * @covers Encrypt_Engine::__construct
	 * @covers Encrypt_Engine_Openssl::__construct
	 */
	public function test_invalid_key_or_cipher_throws_exception($key, $cipher)
	{
		$this->expectException('Kohana_Exception');

		new Encrypt(['key' => $key, 'cipher' => $cipher]);
	}

	/**
	 * @test
	 * @covers Encrypt::instance
	 */
	public function test_instance_uses_config_and_is_singleton()
	{
		$this->setEnvironment([
			'Encrypt::$instances' => [],
			'encrypt.test' => ['type' => 'openssl', 'key' => $this->key],
		]);

		$encrypt = Encrypt::instance('test');

		$this->assertSame($encrypt, Encrypt::instance('test'));
		$this->assertSame('test text', (new Encrypt(['key' => $this->key]))->decode($encrypt->encode('test text')));
	}

	/**
	 * @test
	 * @covers Encrypt::instance
	 */
	public function test_instance_without_key_throws_exception()
	{
		$this->setEnvironment([
			'Encrypt::$instances' => [],
			'encrypt.test' => ['type' => 'openssl'],
		]);

		$this->expectException('Kohana_Exception');
		$this->expectExceptionMessage('No encryption key is defined in the encryption configuration group: test');

		Encrypt::instance('test');
	}
}
