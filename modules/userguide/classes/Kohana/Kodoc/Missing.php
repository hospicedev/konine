<?php
/**
 * Set Kodoc_Missing::create_class as an autoloading to prevent missing classes
 * from crashing the api browser.  Classes that are missing a parent will
 * extend this class, and get a warning in the API browser.
 *
 * @package    Kohana/Userguide
 * @category   Undocumented
 * @author     Kohana Team
 * @copyright  (c) Kohana Team
 * @license    https://koseven.ga/LICENSE.md
 * @since      3.0.7
 */
abstract class Kohana_Kodoc_Missing {

	/**
	 * Creates classes when they are otherwise not found.
	 *
	 *     Kodoc::create_class('ThisClassDoesNotExist');
	 *
	 * [!!] All classes created will extend [Kodoc_Missing].
	 *
	 * @param   string   class name
	 * @return  boolean
	 * @since   3.0.7
	 */
	public static function create_class($class)
	{
		if ( ! class_exists($class))
		{
			// Validate that $class contains only valid PHP class/namespace characters
			if ( ! preg_match('/^[a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff\\\\]*$/', $class))
			{
				return FALSE;
			}

			// Create a new missing class
			if (FALSE === strpos($class, '\\'))
			{
				eval("class {$class} extends Kodoc_Missing {}");
			}
			else
			{
				$namespace = explode('\\', $class);
				$class = array_pop($namespace);
				eval("namespace ".implode('\\', $namespace)."; class {$class} extends \Kodoc_Missing {}");
			}
		}

		return TRUE;
	}

} // End Kohana_Kodoc_Missing
