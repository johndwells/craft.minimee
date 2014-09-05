<?php namespace Craft;

use \Guzzle\Http\Client;
use \Guzzle\Http\ClientInterface;
use \SelvinOrtiz\Zit\Zit;

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
		return '0.9.3';
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
	 * Autoloading, Dependency Injection, Hook & Event binding
	 * 
	 * @return Void
	 */
	public function init()
	{
		$this->_autoload();

		$this->_registerMinimeeDI();

		$this->_bindEvents();
	}

	/**
	 * Logging any messages to Craft.
	 * 
	 * @param String $msg
	 * @param String $level
	 * @param Bool $force
	 * @return Void
	 */
	public static function log($msg, $level = LogLevel::Info, $force = false)
	{
		if(version_compare('2.0', craft()->getVersion(), '>'))
		{
			Craft::log($msg, $level, $force);
		}
		else
		{
			parent::log($msg, $level, $force);
		}
	}

	/**
	 * We define our setting attributes by way of our own Minimee_SettingsModel.
	 * 
	 * @return Array
	 */
	public function defineSettings()
	{
		$this->_autoload();

		// we don't use DI here because defineSettings() may get run without first running init()
		$settings = new Minimee_SettingsModel();

		return $settings->defineAttributes();
	}

	/**
	 * Renders the settings form to configure Minimee
	 * @return String
	 */
	public function getSettingsHtml()
	{
		$filesystemConfigPath = craft()->path->getConfigPath() . 'minimee.php';

		return craft()->templates->render('minimee/settings', array(
			'settings' => $this->getSettings(),
			'filesystemConfigExists' => (bool) IOHelper::fileExists($filesystemConfigPath)

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
		$this->_autoload();

		// we don't use DI here because prepSettings() may get run without first running init()
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
		if(minimee()->service->settings->useResourceCache())
		{
			return array(
				minimee()->service->makePathToStorageFolder() => Craft::t('Minimee caches')
			);
		}
		else
		{
			return array();
		}
	}


	/**
	 * Watch for the "createCache" event, and if in devMode, try to 
	 * clean up any expired caches
	 *
	 * @return void
	 */
	protected function _bindEvents()
	{
		craft()->on('minimee.createCache', function(Event $event) {
			if(craft()->config->get('devMode'))
			{
				minimee()->service->deleteExpiredCache();
			}
		});
	}

	/**
	 * Require any enums used across Minimee
	 *
	 * @return Void
	 */
	protected function _autoload()
	{
		require_once craft()->path->getPluginsPath() . 'minimee/library/vendor/autoload.php';

		Craft::import('plugins.minimee.enums.MinimeeType');
		Craft::import('plugins.minimee.models.Minimee_ISettingsModel');
		Craft::import('plugins.minimee.models.Minimee_SettingsModel');
	}

	/**
	 * Registers all Dependency Injection
	 *
	 * @return Void
	 */
	protected function _registerMinimeeDI()
	{
		minimee()->stash('plugin', $this);
		minimee()->stash('service', craft()->minimee);

		minimee()->extend('makeSettingsModel', function(Zit $zit, $attributes = array()) {
			return new Minimee_SettingsModel($attributes);
		});

		minimee()->extend('makeLocalAssetModel', function(Zit $zit, $attributes = array()) {
			return new Minimee_LocalAssetModel($attributes);
		});

		minimee()->extend('makeRemoteAssetModel', function(Zit $zit, $attributes = array()) {
			return new Minimee_RemoteAssetModel($attributes);
		});

		minimee()->extend('makeClient', function(Zit $zit) {
			return new Client;
		});
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