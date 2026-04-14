<?php

use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestCase;

/**
 * A version of the stock PHPUnit testcase that includes some extra helpers
 * and default settings
 *
 * @package    Kohana/UnitTest
 * @author     Kohana Team
 * @copyright  (c) 2007-2012 Kohana Team
 * @copyright  (c) 2016-2018 Koseven Team
 * @license    https://koseven.ga/LICENSE.md
 */
abstract class Kohana_Unittest_TestCase extends TestCase implements Test {
	
	/**
	 * Make sure PHPUnit backs up globals
	 * @var boolean
	 */
	protected $backupGlobals = FALSE;

	/**
	 * A set of unittest helpers that are shared between normal / database
	 * testcases
	 * @var Kohana_Unittest_Helpers
	 */
	protected $_helpers = NULL;

	/**
	 * A default set of environment to be applied before each test
	 * @var array
	 */
	protected $environmentDefault = [];

	/**
	 * Creates a predefined environment using the default environment
	 *
	 * Extending classes that have their own setUp() should call
	 * parent::setUp()
	 * 
	 * @codeCoverageIgnore
	 */
	public function setUp(): void
	{
		$this->_helpers = new Unittest_Helpers;

		$this->setEnvironment($this->environmentDefault);
	}

	/**
	 * Restores the original environment overriden with setEnvironment()
	 *
	 * Extending classes that have their own tearDown()
	 * should call parent::tearDown()
	 * 
	 * @codeCoverageIgnore
	 */
	public function tearDown(): void
	{
		$this->_helpers->restore_environment();
	}

	/**
	 * Removes all kohana related cache files in the cache directory
	 */
	public function cleanCacheDir()
	{
		return Unittest_Helpers::clean_cache_dir();
	}

	/**
	 * Helper function that replaces all occurences of '/' with
	 * the OS-specific directory separator
	 *
	 * @param string $path The path to act on
	 * @return string
	 */
	public function dirSeparator($path)
	{
		return Unittest_Helpers::dir_separator($path);
	}

	/**
	 * Allows easy setting & backing up of enviroment config
	 *
	 * Option types are checked in the following order:
	 *
	 * * Server Var
	 * * Static Variable
	 * * Config option
	 *
	 * @codeCoverageIgnore
	 * @param array $environment List of environment to set
	 */
	public function setEnvironment(array $environment)
	{
		return $this->_helpers->set_environment($environment);
	}

	/**
	 * Check for internet connectivity
	 *
	 * @return boolean Whether an internet connection is available
	 */
	public function hasInternet()
	{
		return Unittest_Helpers::has_internet();
	}

	/**
	 * Evaluate an HTML or XML string and assert its structure and/or contents.
	 *
	 * NOTE:
	 * Overriding this method to remove the deprecation error
	 * when tested with PHPUnit 4.2.0+
	 *
	 * TODO:
	 * this should be removed when phpunit-dom-assertions gets released
	 * https://github.com/phpunit/phpunit-dom-assertions
	 *
	 * @param array $matcher
	 * @param string $actual
	 * @param string $message
	 * @param bool $isHtml
	 * @uses Unittest_TestCase::tag_match
	 */
	public static function assertTag($matcher, $actual, $message = '', $isHtml = true)
	{
		//trigger_error(__METHOD__ . ' is deprecated', E_USER_DEPRECATED);

		$matched = static::tag_match($matcher, $actual, $message, $isHtml);
		static::assertTrue($matched, $message);
	}

	/**
	 * This assertion is the exact opposite of assertTag
	 *
	 * Rather than asserting that $matcher results in a match, it asserts that
	 * $matcher does not match
	 *
	 * NOTE:
	 * Overriding this method to remove the deprecation error
	 * when tested with PHPUnit 4.2.0+
	 *
	 * TODO:
	 * this should be removed when phpunit-dom-assertions gets released
	 * https://github.com/phpunit/phpunit-dom-assertions
	 *
	 * @param array $matcher
	 * @param string $actual
	 * @param string $message
	 * @param bool $isHtml
	 * @uses Unittest_TestCase::tag_match
	 */
	public static function assertNotTag($matcher, $actual, $message = '', $isHtml = true)
	{

		//trigger_error(__METHOD__ . ' is deprecated', E_USER_DEPRECATED);
        
		$matched = static::tag_match($matcher, $actual, $message, $isHtml);
		static::assertTrue($matched, $message);
	}

	/**
	 * Helper function to match HTML string tags against certain criteria
	 *
	 * TODO:
	 * this should be removed when phpunit-dom-assertions gets released
	 * https://github.com/phpunit/phpunit-dom-assertions
	 *
	 * @param array $matcher
	 * @param string $actual
	 * @param string $message
	 * @param bool $isHtml
	 * @return bool TRUE if there is a match FALSE otherwise
	 */
	protected static function tag_match($matcher, $actual, $message = '', $isHtml = true)
	{
		$dom = new DOMDocument;
		if ($isHtml) {
			@$dom->loadHTML($actual);
		} else {
			@$dom->loadXML($actual);
		}
		$tags = $dom->getElementsByTagName($matcher['tag']);
		return count($tags) > 0 && $tags[0] instanceof DOMNode;
	}

	/**
	 * Helper: read a (possibly protected/private) property from an object using reflection.
	 */
	protected function getObjectProperty(object $object, string $property)
	{
		$reflection = new ReflectionClass($object);
		// Walk up the class hierarchy to find the property
		while (!$reflection->hasProperty($property) && $reflection->getParentClass()) {
			$reflection = $reflection->getParentClass();
		}
		$prop = $reflection->getProperty($property);
		$prop->setAccessible(true);
		return $prop->getValue($object);
	}

	/**
	 * PHPUnit 10 compatibility: assertAttributeSame was removed.
	 */
	public static function assertAttributeSame($expected, string $attribute, object $object, string $message = ''): void
	{
		$reflection = new ReflectionClass($object);
		while (!$reflection->hasProperty($attribute) && $reflection->getParentClass()) {
			$reflection = $reflection->getParentClass();
		}
		$prop = $reflection->getProperty($attribute);
		$prop->setAccessible(true);
		static::assertSame($expected, $prop->getValue($object), $message);
	}

	/**
	 * PHPUnit 10 compatibility: assertAttributeNotSame was removed.
	 */
	public static function assertAttributeNotSame($expected, string $attribute, object $object, string $message = ''): void
	{
		$reflection = new ReflectionClass($object);
		while (!$reflection->hasProperty($attribute) && $reflection->getParentClass()) {
			$reflection = $reflection->getParentClass();
		}
		$prop = $reflection->getProperty($attribute);
		$prop->setAccessible(true);
		static::assertNotSame($expected, $prop->getValue($object), $message);
	}

	/**
	 * PHPUnit 10 compatibility: assertAttributeContains was removed.
	 * Handles both array/Traversable and string haystacks.
	 */
	public static function assertAttributeContains($needle, string $attribute, object $object, string $message = ''): void
	{
		$reflection = new ReflectionClass($object);
		while (!$reflection->hasProperty($attribute) && $reflection->getParentClass()) {
			$reflection = $reflection->getParentClass();
		}
		$prop = $reflection->getProperty($attribute);
		$prop->setAccessible(true);
		$value = $prop->getValue($object);
		if (is_string($value)) {
			static::assertStringContainsString((string) $needle, $value, $message);
		} else {
			static::assertContains($needle, $value, $message);
		}
	}

	/**
	 * PHPUnit 10 compatibility: assertAttributeNotContains was removed.
	 * Handles both array/Traversable and string haystacks.
	 */
	public static function assertAttributeNotContains($needle, string $attribute, object $object, string $message = ''): void
	{
		$reflection = new ReflectionClass($object);
		while (!$reflection->hasProperty($attribute) && $reflection->getParentClass()) {
			$reflection = $reflection->getParentClass();
		}
		$prop = $reflection->getProperty($attribute);
		$prop->setAccessible(true);
		$value = $prop->getValue($object);
		if (is_string($value)) {
			static::assertStringNotContainsString((string) $needle, $value, $message);
		} else {
			static::assertNotContains($needle, $value, $message);
		}
	}

	/**
	 * PHPUnit 10 compatibility: readAttribute() was removed.
	 * Handles class name strings for static properties (pass null to getValue).
	 */
	public static function readAttribute($object, string $attribute)
	{
		$className = is_string($object) ? $object : get_class($object);
		$reflection = new ReflectionClass($className);
		while (!$reflection->hasProperty($attribute) && $reflection->getParentClass()) {
			$reflection = $reflection->getParentClass();
		}
		$prop = $reflection->getProperty($attribute);
		$prop->setAccessible(true);
		// Static properties require null; instance properties need the object
		return $prop->isStatic() ? $prop->getValue(null) : $prop->getValue($object);
	}

	/**
	 * PHPUnit 10 compatibility: assertInternalType() was removed.
	 */
	public static function assertInternalType(string $type, $actual, string $message = ''): void
	{
		$method = 'assertIs' . ucfirst($type);
		if (method_exists(static::class, $method)) {
			static::$method($actual, $message);
		} else {
			static::assertThat($actual, new \PHPUnit\Framework\Constraint\IsType($type), $message);
		}
	}
}
