<?php
namespace Craft;

use \SelvinOrtiz\Zit\Zit;

class MinimeeBaseTest extends BaseTest
{
	protected function _inspect($data)
	{
		fwrite(STDERR, print_r($data));
	}
}

/**
 * A way to grab the dependency container within the Craft namespace
 */
if (!function_exists('\\Craft\\minimee'))
{
	function minimee()
	{
		return Zit::getInstance();
	}
}