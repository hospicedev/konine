# Cookies

Kohana provides classes that make it easy to work with both cookies and sessions. At a high level both sessions and cookies provide the same functionality. They allow the developer to store temporary or persistent information about a specific client for later retrieval, usually to make something persistent between requests.

[Cookies](http://en.wikipedia.org/wiki/HTTP_cookie) should be used for storing non-private data that is persistent for a long period of time. For example storing a user preference or a language setting. Use the [Cookie] class for getting and setting cookies.

[!!] Kohana uses "signed" cookies. Every cookie that is stored is combined with a secure hash to prevent modification of the cookie.  If a cookie is modified outside of Kohana the hash will be incorrect and the cookie will be deleted.  This hash is generated using [Cookie::salt()], which uses the [Cookie::$salt] property. You must define this setting in your bootstrap.php:

	Cookie::$salt = 'foobar';

Or define an extended cookie class in your application:

	class Cookie extends Kohana_Cookie
	{
		public static $salt = 'foobar';
	}

You should set the salt to a secure value. The example above is only for demonstrative purposes.

Nothing stops you from using `$_COOKIE` like normal, but you can not mix using the Cookie class and the regular `$_COOKIE` global, because the hash that Kohana uses to sign cookies will not be present, and Kohana will delete the cookie.

## Storing, Retrieving, and Deleting Data

[Cookie] and [Session] provide a very similar API for storing data. The main difference between them is that sessions are accessed using an object, and cookies are accessed using a static class.

### Storing Data

Storing session or cookie data is done using the [Cookie::set] method:

    // Set cookie data
    Cookie::set($key, $value);

    // Store a user id
    Cookie::set('user_id', 10);

### Retrieving Data

Getting session or cookie data is done using the [Cookie::get] method:

    // Get cookie data
    $data = Cookie::get($key, $default_value);

    // Get the user id
    $user = Cookie::get('user_id');

### Deleting Data

Deleting session or cookie data is done using the [Cookie::delete] method:
    
    // Delete cookie data
    Cookie::delete($key);

    // Delete the user id
    Cookie::delete('user_id');

## Cookie Settings

All of the cookie settings are changed using static properties. You can either change these settings in `bootstrap.php` or by using [transparent extension](extension).  Always check these settings before making your application live, as many of them will have a direct affect on the security of your application.

The most important setting is [Cookie::$salt], which is used for secure signing. This value should be changed and kept secret:

    Cookie::$salt = 'your secret is safe with me';

[!!] Changing this value will render all cookies that have been set before invalid.

By default, cookies are stored until the browser is closed. To use a specific lifetime, change the [Cookie::$expiration] setting:

    // Set cookies to expire after 1 week
    Cookie::$expiration = 604800;

    // Alternative to using raw integers, for better clarity
    Cookie::$expiration = Date::WEEK;

The path that the cookie can be accessed from can be restricted using the [Cookie::$path] setting.

    // Allow cookies only when going to /public/*
    Cookie::$path = '/public/';

The domain that the cookie can be accessed from can also be restricted, using the [Cookie::$domain] setting.

    // Allow cookies only on the domain www.example.com
    Cookie::$domain = 'www.example.com';

If you want to make the cookie accessible on all subdomains, use a dot at the beginning of the domain.

    // Allow cookies to be accessed on example.com and *.example.com
    Cookie::$domain = '.example.com';

To only allow the cookie to be accessed over a secure (HTTPS) connection, use the [Cookie::$secure] setting.

    // Allow cookies to be accessed only on a secure connection
    Cookie::$secure = TRUE;
    
    // Allow cookies to be accessed on any connection
    Cookie::$secure = FALSE;

To prevent cookies from being accessed using Javascript, you can change the [Cookie::$httponly] setting.

    // Make cookies inaccessible to Javascript
    Cookie::$httponly = TRUE;

## SameSite

The [Cookie::$samesite] setting adds a `SameSite` attribute to every cookie that the [Cookie] class sets or deletes. It controls whether the browser sends the cookie with cross-site requests.

    // Recommended for most applications, including session cookies
    Cookie::$samesite = 'Lax';

| Value | Behaviour |
|-------|-----------|
| `NULL` | Default. No `SameSite` attribute is sent, so cookies behave exactly as in earlier releases. Browsers then apply their own default, which varies between browsers. |
| `'Lax'` | The cookie is sent with same-site requests and with top-level cross-site navigations using safe methods (for example following a link). It is not sent with cross-site `POST` requests, images or frames. Recommended for session cookies. |
| `'Strict'` | The cookie is only sent with same-site requests. The user appears logged out when arriving from another site, including OAuth/SSO redirects, payment provider returns and links in emails. |
| `'None'` | The cookie is sent with all requests, including cross-site ones. Requires [Cookie::$secure] to be `TRUE`. |

Values are case-insensitive (`'lax'` is sent as `Lax`). Any other value throws a [Kohana_Exception] on the first cookie operation, as does `'None'` unless `Cookie::$secure` is the boolean `TRUE` (truthy values such as `1` are not accepted). Browsers silently reject `SameSite=None` cookies that are not `Secure`, so Konine throws instead of silently turning `Secure` on.

[!!] The first cookie operation may not be where you expect. [Cookie::get] throws when it deletes a cookie with an invalid signature, and when a session is written at the end of a request, [Session::write] catches the exception and only logs it, so sessions are silently lost. Test your configuration after changing `Cookie::$samesite`, for example by calling `Cookie::set()` once in a development environment.

[!!] `'None'` lowers your protection against cross-site request forgery (CSRF). Some browsers treat cookies without a `SameSite` attribute as `Lax`, so setting `'None'` explicitly turns cross-site sending back on. Only use it when you need it, for example for embedded or cross-site SSO flows, and always combine it with token-based CSRF protection.

### Things to be aware of

- **SameSite complements CSRF tokens. It does not replace them.** "Same-site" is not the same as "same-origin": sibling subdomains (such as `a.example.com` and `b.example.com`) count as the same site. `Lax` still sends cookies with top-level cross-site `GET` navigations. Keep using [Security::token] for state-changing requests.
- **The setting is global.** It applies to every cookie set through the [Cookie] class, including the session cookie used by [Session_Cookie] and [Session_Database]. Setting `'None'` for one embed or SSO cookie therefore also changes the session cookie.
- **[Session_Native] is not covered.** Native sessions set their cookie through PHP's `session_set_cookie_params()`, not the [Cookie] class. Configure `session.cookie_samesite` in `php.ini` (or with `ini_set()`) for native sessions. The only exception is when a native session is destroyed: the cookie that deletes it is sent through [Cookie::delete], so it does get the `SameSite` attribute (and is validated).
- **Overriding `Cookie::_setcookie()`.** When `Cookie::$samesite` is `NULL`, cookies are still sent through the protected `Cookie::_setcookie()` method, as before. When it is set, they are sent through `Cookie::_setcookie_with_options($name, $value, array $options)` instead. If your application overrides or mocks `_setcookie()` and you enable SameSite, override `_setcookie_with_options()` as well.
