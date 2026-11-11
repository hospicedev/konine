<?php
/**
 * Tests the deprecation of the APC cache driver in favour of APCu.
 *
 * @package    Kohana/Cache
 * @group      kohana
 * @group      kohana.cache
 * @category   Test
 * @author     Kohana Team
 * @copyright  (c) Kohana Team
 * @license    https://github.com/hospicedev/konine/blob/master/LICENSE.md
 * @see        https://github.com/hospicedev/konine/issues/19
 */
class Kohana_ApcDeprecationTest extends Unittest_TestCase {

	/**
	 * Provider for test_class_is_marked_deprecated
	 *
	 * @return  array
	 */
	public static function provider_apc_classes()
	{
		return [
			['Kohana_Cache_Apc'],
			['Cache_Apc'],
		];
	}

	/**
	 * Both the framework class and the transparent extension carry a
	 * @deprecated notice pointing to the APCu driver.
	 *
	 * @dataProvider provider_apc_classes
	 *
	 * @param   string  $class  class name
	 * @return  void
	 */
	public function test_class_is_marked_deprecated($class)
	{
		$doc = (string) (new ReflectionClass($class))->getDocComment();

		$this->assertMatchesRegularExpression('/@deprecated\b.*Cache_Apcu/', $doc);
	}

	/**
	 * The constructor still throws the same Cache_Exception, with the same
	 * start of message, but now points to the apcu driver.
	 *
	 * @return  void
	 */
	public function test_constructor_exception_points_to_apcu()
	{
		if (extension_loaded('apc'))
		{
			$this->markTestSkipped('The APC extension is loaded, so the constructor does not throw');
		}

		$class = new ReflectionClass('Cache_Apc');
		$cache = $class->newInstanceWithoutConstructor();

		try
		{
			$class->getConstructor()->invoke($cache, ['driver' => 'apc']);
			$this->fail('Expected Cache_Exception was not thrown');
		}
		catch (Cache_Exception $e)
		{
			$this->assertStringStartsWith('PHP APC extension is not available.', $e->getMessage());
			$this->assertStringContainsString("'apcu'", $e->getMessage());
		}
	}

}
