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
		return $this->_run(MinimeeType::Css, $assets, $settings);
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
		return $this->_run(MinimeeType::Js, $assets, $settings);
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
		MinimeePlugin::log(Craft::t('MinimeeVariable::' . $type . '() is being run now.'));

		$minified = minimee()->service->run($type, $assets, $settings);

		if( ! $minified)
		{
			$html = minimee()->service->makeTagsByType($type, $assets);
			return minimee()->service->makeTwigMarkupFromHtml($html);
		}

		$html = minimee()->service->makeTagsByType($type, $minified);
		return minimee()->service->makeTwigMarkupFromHtml($html);
	}
}