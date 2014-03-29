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
		return '0.8.1';
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
		$filesystemConfigPath = CRAFT_CONFIG_PATH . 'minimee.php';

		return craft()->templates->render('minimee/settings', array(
			'settings' => $this->getSettings(),
			'filesystemConfigExists' => IOHelper::fileExists($filesystemConfigPath)

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

	public function prepSettings($settings)
	{
		Craft::import('plugins.minimee.models.Minimee_SettingsModel');

		$settingsModel = new Minimee_SettingsModel();

		return $settingsModel->prepSettings($settings);
	}

	/**
	 * Enable ability to serve cache assets from resources/minimee folder
	 *
	 * @return String
	 */
	public function getResourcePath($path)
	{
		if (strncmp($path, 'minimee/', 8) === 0)
		{
			return craft()->path->getStoragePath().'minimee/'.substr($path, 8);
		}
	}

	/**
	 * Register our cache path that can then be deleted from CP
	 */
	function registerCachePaths()
	{
		return array(
			craft()->minimee->settings->cachePath => Craft::t('Minimee caches')
		);
	}
}