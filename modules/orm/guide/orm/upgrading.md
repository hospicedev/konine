# Upgrading

## Table aliases

ORM [will now alias the main table](http://dev.kohanaframework.org/issues/4066) in a query to the model's singular object name.
i.e. Prior to 3.2 ORM set the from table like so:

	$this->_db_builder->from($this->_table_name);

As of 3.2 it is now aliased like so:

	$this->_db_builder->from(array($this->_table_name, $this->_object_name));

If you have a model `Model_Order` then when building a query use the alias like so:

	$model->where('order.id', '=', $id);

## Serialization on PHP 8 (`__wakeup()` vs `__unserialize()`)

ORM models are commonly stored in the session (for example the logged-in
`Model_Auth_User`). ORM implements the modern serialization contract with
`ORM::__serialize()` and `ORM::__unserialize()`.

`__serialize()` deliberately captures only the object's data and state:

	['_primary_key_value', '_object', '_changed', '_loaded', '_saved', '_sorting', '_original_values']

Everything else (`$_belongs_to`, `$_has_many`, `$_table_name`, `$_db`, …) is
*not* serialized — it is re-established by `_initialize()` when the object is
unserialized.

### What changed

On PHP 8, once a class defines `__unserialize()`, PHP **no longer calls
`__wakeup()` automatically**. Because `Kohana_ORM` defines `__unserialize()`,
any `__wakeup()` you added to an application subclass would otherwise stop
running after deserialization.

To keep that extension point working, `ORM::__unserialize()` now calls
`$this->__wakeup()` itself, and `ORM::__wakeup()` performs the
`_reload_on_wakeup` reload. The net effect:

- **You do not need to override anything** to get the previous reload behaviour.
- If you keep an existing `__wakeup()` override, call `parent::__wakeup()` from
  it so the reload still happens:

		public function __wakeup()
		{
			parent::__wakeup();

			// ... your post-deserialization logic ...
		}

- New code should prefer overriding `__unserialize()` and calling the parent:

		public function __unserialize(array $data): void
		{
			parent::__unserialize($data);

			// ... your post-deserialization logic ...
		}

You do not need both. Sessions serialized before upgrading continue to
deserialize correctly either way.

## `Auth_ORM::force_login()` rejects unknown users

When `force_login()` is given a username/email string it loads the matching
user before completing the login. Previously, if no row matched, it proceeded
to write a session entry for an **unloaded** ORM stub — a "ghost" login for a
user that does not exist.

`force_login()` now throws a `Kohana_Exception` when the supplied username does
not resolve to a loaded user:

	try
	{
		Auth::instance()->force_login('someone@example.com');
	}
	catch (Kohana_Exception $e)
	{
		// No such user — decide how to respond (404, 403, redirect, …)
	}

Passing an already-loaded ORM user object is unaffected. If your application
previously relied on `force_login()` silently doing nothing for a missing user,
wrap the call in a `try/catch` (or check the user exists first).
