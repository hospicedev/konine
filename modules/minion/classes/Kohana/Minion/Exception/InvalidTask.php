<?php

/**
 * Invalid Task Exception
 *
 * @package    Kohana/Minion
 * @author     Kohana Team
 * @copyright  (c) Kohana Team
 * @license    https://github.com/hospicedev/konine/blob/master/LICENSE.md
 */
class Kohana_Minion_Exception_InvalidTask extends Minion_Exception {

	public function format_for_cli()
	{
		return 'ERROR: '. $this->getMessage().PHP_EOL;
	}

}
