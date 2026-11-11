<?php
/**
 * Cookie helper.
 *
 * @package    Kohana
 * @category   Helpers
 * @author     Kohana Team
 * @copyright  (c) Kohana Team
 * @license    https://github.com/hospicedev/konine/blob/master/LICENSE.md
 */
class Kohana_Cookie {

	/**
	 * @var  string  Magic salt to add to the cookie
	 */
	public static $salt = NULL;

	/**
	 * @var  integer  Number of seconds before the cookie expires
	 */
	public static $expiration = 0;

	/**
	 * @var  string  Restrict the path that the cookie is available to
	 */
	public static $path = '/';

	/**
	 * @var  string  Restrict the domain that the cookie is available to
	 */
	public static $domain = '';

	/**
	 * @var  boolean  Only transmit cookies over secure connections
	 */
	public static $secure = FALSE;

	/**
	 * @var  boolean  Only transmit cookies over HTTP, disabling Javascript access
	 */
	public static $httponly = FALSE;

	/**
	 * SameSite attribute sent with every cookie set or deleted by this class.
	 *
	 * Allowed values are 'Lax', 'Strict' and 'None' (case-insensitive), or
	 * NULL to omit the attribute entirely. NULL is the default and keeps the
	 * behaviour of earlier releases unchanged.
	 *
	 * [!!] 'None' requires Cookie::$secure to be TRUE, and lowers CSRF
	 *      protection. Any other value throws a Kohana_Exception on the first
	 *      cookie operation.
	 *
	 * @var  string|NULL  SameSite attribute: 'Lax', 'Strict', 'None', or NULL to omit (default)
	 */
	public static $samesite = NULL;

	/**
	 * @var  array  Allowed SameSite values, keyed by their lower-case form
	 */
	protected static $_samesite_values = [
		'lax'    => 'Lax',
		'strict' => 'Strict',
		'none'   => 'None',
	];

	/**
	 * Gets the value of a signed cookie. Cookies without signatures will not
	 * be returned. If the cookie signature is present, but invalid, the cookie
	 * will be deleted.
	 *
	 *     // Get the "theme" cookie, or use "blue" if the cookie does not exist
	 *     $theme = Cookie::get('theme', 'blue');
	 *
	 * @param   string  $key        cookie name
	 * @param   mixed   $default    default value to return
	 * @return  string
	 */
	public static function get($key, $default = NULL)
	{
		if ( ! isset($_COOKIE[$key]))
		{
			// The cookie does not exist
			return $default;
		}

		// Get the cookie value
		$cookie = $_COOKIE[$key];

		// Find the position of the split between salt and contents
		$split = strlen(Cookie::salt($key, NULL));

		if (isset($cookie[$split]) AND $cookie[$split] === '~')
		{
			// Separate the salt and the value
			list ($hash, $value) = explode('~', $cookie, 2);

			if (Security::slow_equals(Cookie::salt($key, $value), $hash))
			{
				// Cookie signature is valid
				return $value;
			}

			// The cookie signature is invalid, delete it
			static::delete($key);
		}

		return $default;
	}

	/**
	 * Sets a signed cookie. Note that all cookie values must be strings and no
	 * automatic serialization will be performed!
	 *
	 * [!!] By default, Cookie::$expiration is 0 - if you skip/pass NULL for the optional
	 *      lifetime argument your cookies will expire immediately unless you have separately
	 *      configured Cookie::$expiration.
	 *
	 *
	 *     // Set the "theme" cookie
	 *     Cookie::set('theme', 'red');
	 *
	 * @param   string  $name       name of cookie
	 * @param   string  $value      value of cookie
	 * @param   integer $lifetime   lifetime in seconds
	 * @return  boolean
	 * @uses    Cookie::salt
	 */
	public static function set($name, $value, $lifetime = NULL)
	{
		if ($lifetime === NULL)
		{
			// Use the default expiration
			$lifetime = Cookie::$expiration;
		}

		if ($lifetime !== 0)
		{
			// The expiration is expected to be a UNIX timestamp
			$lifetime += static::_time();
		}

		// Add the salt to the cookie value
		$value = Cookie::salt($name, $value).'~'.$value;

		return static::_dispatch($name, $value, $lifetime);
	}

	/**
	 * Deletes a cookie by making the value an empty string and expiring it.
	 *
	 *     Cookie::delete('theme');
	 *
	 * @param   string  $name   cookie name
	 * @return  boolean
	 */
	public static function delete($name)
	{
		// Remove the cookie
		unset($_COOKIE[$name]);

		// Empty the cookie and make it expire
		return static::_dispatch($name, '', -86400);
	}

	/**
	 * Sends a cookie using the configured path, domain, secure, httponly and
	 * samesite settings.
	 *
	 * When Cookie::$samesite is NULL (the default) this calls
	 * [Cookie::_setcookie] with exactly the same arguments as earlier
	 * releases, so existing overrides keep working. Otherwise it calls
	 * [Cookie::_setcookie_with_options].
	 *
	 * @param   string  $name   name of cookie
	 * @param   string  $value  value of cookie
	 * @param   integer $expire expiry as a UNIX timestamp, or 0 for a session cookie
	 * @return  boolean
	 * @throws  Kohana_Exception  if Cookie::$samesite is invalid
	 */
	protected static function _dispatch($name, $value, $expire)
	{
		if (Cookie::$samesite === NULL)
		{
			// Legacy path: unchanged, so existing _setcookie() overrides still apply
			return static::_setcookie($name, $value, $expire, Cookie::$path, Cookie::$domain, Cookie::$secure, Cookie::$httponly);
		}

		return static::_setcookie_with_options($name, $value, [
			'expires'  => $expire,
			'path'     => Cookie::$path,
			'domain'   => Cookie::$domain,
			'secure'   => Cookie::$secure,
			'httponly' => Cookie::$httponly,
			'samesite' => static::_samesite(),
		]);
	}

	/**
	 * Validates Cookie::$samesite and returns its normalised form, for
	 * example 'lax' becomes 'Lax'. Only the allowed values can reach the
	 * Set-Cookie header.
	 *
	 * @return  string  'Lax', 'Strict' or 'None'
	 * @throws  Kohana_Exception  if the value is not allowed, or is 'None' without Cookie::$secure set to TRUE
	 */
	protected static function _samesite()
	{
		$samesite = Cookie::$samesite;

		if ( ! is_string($samesite) OR ! isset(static::$_samesite_values[strtolower($samesite)]))
		{
			throw new Kohana_Exception('Invalid Cookie::$samesite value. Allowed values are NULL, \'Lax\', \'Strict\' and \'None\'.');
		}

		$samesite = static::$_samesite_values[strtolower($samesite)];

		if ($samesite === 'None' AND Cookie::$secure !== TRUE)
		{
			throw new Kohana_Exception('Cookie::$samesite \'None\' requires Cookie::$secure to be TRUE. Browsers reject SameSite=None cookies that are not Secure.');
		}

		return $samesite;
	}

	/**
	 * Generates a salt string for a cookie based on the name and value.
	 *
	 *     $salt = Cookie::salt('theme', 'red');
	 *
	 * @param   string $name name of cookie
	 * @param   string $value value of cookie
	 *
	 * @throws Kohana_Exception if Cookie::$salt is not configured
	 * @return  string
	 */
	public static function salt($name, $value)
	{
		// Require a valid salt
		if ( ! Cookie::$salt)
		{
			throw new Kohana_Exception('A valid cookie salt is required. Please set Cookie::$salt in your bootstrap.php. For more information check the documentation');
		}

		// Determine the user agent
		$agent = isset($_SERVER['HTTP_USER_AGENT']) ? strtolower($_SERVER['HTTP_USER_AGENT']) : 'unknown';

		return hash_hmac('sha1', $agent.$name.$value.Cookie::$salt, Cookie::$salt);
	}

	/**
	 * Proxy for the native setcookie function - to allow mocking in unit tests so that they do not fail when headers
	 * have been sent.
	 *
	 * @param string  $name
	 * @param string  $value
	 * @param integer $expire
	 * @param string  $path
	 * @param string  $domain
	 * @param boolean $secure
	 * @param boolean $httponly
	 *
	 * @return bool
	 * @see setcookie
	 */
	protected static function _setcookie($name, $value, $expire, $path, $domain, $secure, $httponly)
	{
		return setcookie($name, $value, $expire, $path, $domain, $secure, $httponly);
	}

	/**
	 * Proxy for the native setcookie function using its options array form,
	 * used when Cookie::$samesite is set. Override it to mock cookies in unit
	 * tests.
	 *
	 * [!!] Applications that override [Cookie::_setcookie] and set
	 *      Cookie::$samesite must also override this method, because
	 *      _setcookie() is not called when SameSite is enabled.
	 *
	 * @param   string  $name     name of cookie
	 * @param   string  $value    value of cookie
	 * @param   array   $options  expires, path, domain, secure, httponly and samesite
	 * @return  boolean
	 * @see     setcookie
	 */
	protected static function _setcookie_with_options($name, $value, array $options)
	{
		return setcookie($name, $value, $options);
	}

	/**
	 * Proxy for the native time function - to allow mocking of time-related logic in unit tests
	 *
	 * @return int
	 * @see    time
	 */
	protected static function _time()
	{
		return time();
	}

}
