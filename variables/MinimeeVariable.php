<?php namespace Craft;

/**
 * Minimee by John D Wells
 *
 * @author     	John D Wells <http://johndwells.com>
 * @package    	Minimee
 * @since		Craft 1.3
 * @copyright 	Copyright (c) 2014, John D Wells
 * @license 	http://opensource.org/licenses/mit-license.php MIT License
 * @link       	http://github.com/johndwells/Minimee-Craft
 */

/**
 * 
 */
class MinimeeVariable
{
	public function css($assets, $settings = array())
	{
		return $this->_run('css', $assets, $settings);
	}

	public function js($assets, $settings = array())
	{
		return $this->_run('js', $assets, $settings);
	}

	protected function _run($type, $assets, $settings = array())
	{
		$minified = craft()->minimee->setSettings($settings)->$type($assets);

		// false means we failed, so return original markup
		if( ! $minified)
		{
			$html = craft()->minimee_helper->makeTagsByType($type, $assets);
			return craft()->minimee_helper->returnHtmlAsTwigMarkup($html);
		}

		$html = craft()->minimee_helper->makeTagsByType($type, $minified);
		return craft()->minimee_helper->returnHtmlAsTwigMarkup($html);

	}
}