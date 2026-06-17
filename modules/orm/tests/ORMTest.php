<?php

/**
 * Test case for Kohana_ORM
 *
 * @package    Kohana/ORM
 * @group      kohana
 * @group      kohana.orm
 * @category   Test
 * @author     Craig Duncan <git@duncanc.co.uk>
 * @copyright  (c) Kohana Team
 * @license    https://github.com/hospicedev/konine/blob/master/LICENSE.md
 */

class Kohana_ORMTest extends Unittest_TestCase
{
	/**
	 * Ensure has() doesn't attempt to count non-countables.
	 *
	 * @test
	 * @covers ORM::has
	 */
	public function test_has()
	{
		$orm = new ORM_Example;

		$result = $orm->has('children', FALSE);

		$this->assertSame(FALSE, $result);
	}

	/**
	 * On PHP 8, defining __unserialize() means PHP no longer calls __wakeup()
	 * automatically. ORM::__unserialize() must invoke __wakeup() itself so that
	 * application subclasses relying on the historical __wakeup() extension
	 * point continue to run after deserialization.
	 *
	 * @test
	 * @covers ORM::__unserialize
	 */
	public function test_unserialize_invokes_subclass_wakeup()
	{
		$orm = new ORM_Wakeup_Example;

		$this->assertFalse($orm->wakeup_called);

		$orm->__unserialize([]);

		$this->assertTrue($orm->wakeup_called, '__unserialize() must call __wakeup() for backwards compatibility');
	}

	/**
	 * The default ORM::__wakeup() performs the _reload_on_wakeup reload, so an
	 * object with no __wakeup() override still reloads on deserialization.
	 *
	 * @test
	 * @covers ORM::__wakeup
	 */
	public function test_unserialize_reloads_when_reload_on_wakeup_true()
	{
		$orm = new ORM_Reload_Example;

		$orm->__unserialize([]);

		$this->assertTrue($orm->reloaded, '__wakeup() must reload when _reload_on_wakeup is TRUE');
	}

	/**
	 * When _reload_on_wakeup is FALSE the default __wakeup() must not reload.
	 *
	 * @test
	 * @covers ORM::__wakeup
	 */
	public function test_unserialize_skips_reload_when_reload_on_wakeup_false()
	{
		$orm = new ORM_Reload_Example;
		$orm->set_reload_on_wakeup(FALSE);

		$orm->__unserialize([]);

		$this->assertFalse($orm->reloaded, '__wakeup() must not reload when _reload_on_wakeup is FALSE');
	}
}

class ORM_Example extends Kohana_ORM
{
	public function __construct()
	{
	}
}

/**
 * Subclass that overrides __wakeup() (the legacy extension point) and skips the
 * database-dependent _initialize()/reload() so the b/c behaviour can be tested
 * without a configured database connection.
 */
class ORM_Wakeup_Example extends Kohana_ORM
{
	public $wakeup_called = FALSE;

	public function __construct()
	{
	}

	protected function _initialize()
	{
	}

	public function __wakeup()
	{
		$this->wakeup_called = TRUE;

		// Intentionally does not call parent::__wakeup() to avoid reload().
	}
}

/**
 * Subclass that exercises the default ORM::__wakeup() reload behaviour while
 * stubbing out the database access in _initialize() and reload().
 */
class ORM_Reload_Example extends Kohana_ORM
{
	public $reloaded = FALSE;

	public function __construct()
	{
	}

	public function set_reload_on_wakeup($value)
	{
		$this->_reload_on_wakeup = $value;
	}

	protected function _initialize()
	{
	}

	public function reload()
	{
		$this->reloaded = TRUE;

		return $this;
	}
}
