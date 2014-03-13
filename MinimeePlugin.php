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

	/**
	 * @return String
	 */
	public function getName()
	{
		return 'Minimee';
	}

	/**
	 * @return String
	 */
	public function getVersion()
	{
		return '0.7.2';
	}

	/**
	 * @return String
	 */
	public function getDeveloper()
	{
		return 'John D Wells';
	}

	/**
	 * @return String
	 */
	public function getDeveloperUrl()
	{
		return 'http://johndwells.com';
	}

	/**
	 * @return Bool
	 */
	public function hasCpSection()
	{
		return false;
	}

	/**
	 * Hook & Event binding is done during initialisation
	 * 
	 * @return Void
	 */
	public function init()
	{
		craft()->on('minimee.createCache', function(Event $event) {
			if(craft()->config->get('devMode'))
			{
				craft()->minimee->deleteExpiredCache();
			}
		});
	}

	/**
	 * We define our setting attributes by way of our own Minimee_SettingsModel.
	 * 
	 * @return Array
	 */
	public function defineSettings()
	{
		// use our settings model to define settings
		Craft::import('plugins.minimee.models.Minimee_SettingsModel');

		$settings = new Minimee_SettingsModel();

		return $settings->defineAttributes();
	}

	/**
	 * Renders the settings form to configure Minimee
	 * @return String
	 */
	public function getSettingsHtml()
	{
		return craft()->templates->render('minimee/settings', array(
			'settings' => $this->getSettings()
		));
	}

	/**
	 * Register our Twig filter
	 *
	 * @return Twig_Extension
	 **/
	public function addTwigExtension()
	{
		Craft::import('plugins.minimee.twigextensions.MinimeeTwigExtension');

		return new MinimeeTwigExtension();
	}

	/**
	 * Enable ability to serve cache assets from resources/minimee folder
	 *
	 * @return String
	 */
	public function getResourcePath($path)
	{
		// Are they requesting a drink image?
		if (strncmp($path, 'minimee/', 8) === 0)
		{
			return craft()->path->getStoragePath().'minimee/'.substr($path, 8);
		}
	}
}