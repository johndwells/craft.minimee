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
	/**
	 * Our {{ craft.minimee.css() }} variable tag
	 *
	 * @param Array $assets
	 * @param Array $settings
	 * @return String
	 */
	public function css($assets, $settings = array())
	{
		return $this->_run('css', $assets, $settings);
	}

	/**
	 * Our {{ craft.minimee.js() }} variable tag
	 *
	 * @param Array $assets
	 * @param Array $settings
	 * @return String
	 */
	public function js($assets, $settings = array())
	{
		return $this->_run('js', $assets, $settings);
	}

	/**
	 * Internal function to run variable tags
	 *
	 * @param String $type
	 * @param Array $assets
	 * @param Array $settings
	 * @return String
	 */
	protected function _run($type, $assets, $settings = array())
	{
		Craft::log(Craft::t('MinimeeVariable::' . $type . '() is being run now.'));

		$minified = craft()->minimee->$type($assets, $settings);

		if( ! $minified)
		{
			$html = craft()->minimee->makeTagsByType($type, $assets);
			return craft()->minimee->returnHtmlAsTwigMarkup($html);
		}

		$html = craft()->minimee->makeTagsByType($type, $minified);
		return craft()->minimee->returnHtmlAsTwigMarkup($html);
	}
}