<?php
/**
 * UTF8::strtolower
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  (c) Kohana Team
 * @copyright  (c) 2005 Harry Fuecks
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt
 */
function _strtolower($str)
{
	if (UTF8::is_ascii($str))
		return strtolower($str);

	return mb_strtolower($str, 'UTF-8');
}
