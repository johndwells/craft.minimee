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
class MinimeePlugin extends BasePlugin
{
	public function getName()
	{
		return 'Minimee';
	}

	public function getVersion()
	{
		return '0.6.2';
	}

	/**
	 * Prepares plugin settings prior to saving them to the db
	 *
	 * @param	array	$settings	The settings array from $_POST provided by Craft
	 * @return	array
	 */
	public function prepare(array $settings=array())
	{
		$settings['enabled']	= (bool) $settings['enabled'];

		return $settings;
	}

	public function getDeveloper()
	{
		return 'John D Wells';
	}

	public function getDeveloperUrl()
	{
		return 'http://johndwells.com';
	}

	public function hasCpSection()
	{
		return false;
	}

	public function init()
	{
		craft()->on('minimee.createCache', function(Event $event) {
			if(craft()->config->get('devMode'))
			{
				craft()->minimee->deleteExpiredCache();
			}
		});
	}

	public function defineSettings()
	{
		// use our settings model to define settings
		Craft::import('plugins.minimee.models.Minimee_SettingsModel');

		$settings = new Minimee_SettingsModel();

		return $settings->defineAttributes();
	}

	public function getSettingsHtml()
	{
		return craft()->templates->render('minimee/settings', array(
			'settings' => $this->getSettings()
		));
	}

	public function addTwigExtension()
	{
		Craft::import('plugins.minimee.twigextensions.MinimeeTwigExtension');

		return new MinimeeTwigExtension();
	}

	public function getResourcePath($path)
	{
		// Are they requesting a drink image?
		if (strncmp($path, 'minimee/', 8) === 0)
		{
			return craft()->path->getStoragePath().'minimee/'.substr($path, 8);
		}
	}
}