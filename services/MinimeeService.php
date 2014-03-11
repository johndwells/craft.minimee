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
class MinimeeService extends BaseApplicationComponent
{
	protected $_assets                  = array();	// array of Minimee_AssetBaseModel
	protected $_type                    = '';		// css or js
	protected $_cache 					= null; 	// instance of Minimee_CacheBaseModel
	protected $_cacheFilename           = '';       // lastmodified value for cache
	protected $_cacheFilenameHash       = '';       // a hash of all asset filenames together
	protected $_cacheFilenameTimestamp  = '';       // eventual filename of cache
	protected $_settings                = null;		// instance of Minimee_SettingsModel


	/**
	 * During startup, fetch settings from our plugin
	 *
	 * @return Void
	 */
	public function init()
	{
		parent::init();

		$this->setSettings(array());
	}

	/**
	 * @return Array
	 */
	public function getAssets()
	{
		return $this->_assets;
	}

	/**
	 * @return String
	 */
	public function getCacheFilename()
	{
		if($this->settings->isResourceCache())
		{
			return $this->cacheFilenameHash . '.' . $this->type;
		}

		return $this->cacheFilenameHash . '.' . $this->cacheFilenameTimestamp . '.' . $this->type;
	}

	/**
	 * @return String
	 */
	public function getCacheFilenameHash()
	{
		return sha1($this->_cacheFilenameHash);
	}

	/**
	 * @return String
	 */
	public function getCacheFilenameHashPath()
	{
		return $this->settings->cachePath . $this->cacheFilenameHash;
	}

	/**
	 * @return String
	 */
	public function getCacheFilenamePath()
	{
		return $this->settings->cachePath . $this->cacheFilename;
	}

	/**
	 * @return String
	 */
	public function getCacheFilenameUrl()
	{
		if($this->settings->isResourceCache())
		{
			$dateParam = craft()->resources->dateParam;
			$params[$dateParam] = $this->cacheFilenameTimestamp;

			return UrlHelper::getResourceUrl('minimee/' . $this->cacheFilename, $params);
		}
		
		return $this->settings->cacheUrl . $this->cacheFilename;
	}

	/**
	 * @return String
	 */
	public function getCacheFilenameTimestamp()
	{
		return ($this->_cacheFilenameTimestamp == 0) ? '0000000000' : $this->_cacheFilenameTimestamp;
	}

	/**
	 * @return Minimee_SettingsModel
	 */
	public function getSettings()
	{
		return $this->_settings;
	}

	/**
	 * @return String
	 */
	public function getType()
	{
		return $this->_type;
	}

	/**
	 * @param Array $assets
	 * @return this
	 */
	public function setAssets($assets)
	{
		foreach($assets as $asset)
		{
			if (craft()->minimee_helper->isUrl($asset))
			{
				$model = array(
					'filename' => $asset,
					'filenameUrl' => $asset,
					'filenamePath' => $asset,
					'type' => $this->type
				);

				$this->_assets[] = Minimee_RemoteAssetModel::populateModel($model);
			}
			else
			{
				$model = array(
					'filename' => $asset,
					'filenameUrl' => $this->settings->baseUrl . $asset,
					'filenamePath' => $this->settings->filesystemPath . $asset,
					'type' => $this->type
				);

				$this->_assets[] = Minimee_LocalAssetModel::populateModel($model);
			}
		}

		return $this;
	}

	/**
	 * @param DateTime $lastTimeModified
	 * @return Void
	 */
	public function setCacheFilenameTimestamp(DateTime $lastTimeModified)
	{
		$timestamp = $lastTimeModified->getTimestamp();
		$this->_cacheFilenameTimestamp = max($this->cacheFilenameTimestamp, $timestamp);
	}

	/**
	 * @param String $name
	 * @return Void
	 */
	public function setCacheFilenameHash($name)
	{
		// remove any cache-busting strings so the cache name doesn't change with every edit.
		// format: .v.1330213450
		// this is held over from EE. Still a good idea to do something like this, perhaps improve in future.
		$this->_cacheFilenameHash .= preg_replace('/\.v\.(\d+)/i', '', $name);
	}

	/**
	 * Configure our service based off the settings in plugin,
	 * allowing plugin settings to be overridden at runtime.
	 *
	 * @param Array $settingsOverrides
	 * @return void
	 */
	public function setSettings($settingsOverrides)
	{
		$plugin = craft()->plugins->getPlugin('minimee');

		$pluginSettings = $plugin->getSettings()->getAttributes();

		$runtimeSettings = (is_array($settingsOverrides)) ? array_merge($pluginSettings, $settingsOverrides) : $pluginSettings;

		$this->_settings = Minimee_SettingsModel::populateModel($runtimeSettings);

		return $this;
	}

	/**
	 * @param String $type
	 * @return this
	 */
	public function setType($type)
	{
		$this->type = $type;

		return $this;
	}

	/**
	 * Main service function that encapsulates an entire Minimee run
	 *
	 * @param String $type
	 * @param Array $assets
	 * @return String|Bool
	 */
	public function run($type, $assets)
	{
		$assets = (is_array($assets)) ? $assets : array($assets);

		try
		{
			return $this->reset()
						->setType($type)
						->setAssets($assets)
						->flightcheck()
						->checkHeaders()
						->cache();
		}
		catch (Exception $e)
		{
			return $this->_abort($e);
		}
	}

	/**
	 * Shorthand function to process CSS
	 *
	 * @param Array $assets
	 * @return String|Bool
	 */
	public function css($assets)
	{
		return $this->run('css', $assets);
	}

	/**
	 * Shorthand function to process JS
	 *
	 * @param Array $assets
	 * @return String|Bool
	 */
	public function js($assets)
	{
		return $this->run('js', $assets);
	}

	/**
	 * Internal function used when aborting due to error
	 *
	 * @param String $e
	 * @return Bool
	 */
	protected function _abort($e)
	{
		Craft::log($e, LogLevel::Warning);

		if(craft()->config->get('devMode') && $this->settings->enabled)
		{
			throw new Exception($e);
		}

		return false;
	}

	/**
	 * Safely resets service to prepare for a clean run.
	 *
	 * @return this
	 */
	public function reset()
	{
		$this->_assets                          = array();
		$this->_type                            = '';
		$this->_cacheFilename                   = '';
		$this->_cacheFilenameHash               = '';
		$this->_cacheFilenameTimestamp          = '';

		return $this;
	}

	/**
	 * Perform pre-flight checks to ensure we can run.
	 *
	 * @return this
	 */
	public function flightcheck()
	{
		if ($this->settings === null)
		{
			throw new Exception(Craft::t('Not installed.'));
		}

		if( ! $this->settings->enabled)
		{
			throw new Exception(Craft::t('Disabled via settings.'));
		}

		if( ! $this->settings->validate())
		{
			$exceptionErrors = '';
			foreach($this->settings->getErrors() as $error)
			{
				$exceptionErrors .= implode('. ', $error);
			}

			throw new Exception(Craft::t('Invalid plugin settings: ') . $exceptionErrors);
		}

		IOHelper::ensureFolderExists($this->settings->cachePath);
		if( ! IOHelper::isWritable($this->settings->cachePath))
		{
			throw new Exception(Craft::t('Cache folder is not writable: ' . $this->settings->cachePath));
		}

		return $this;
	}

	/**
	 * Validate all assets prior to run.
	 *
	 * @return this
	 */
	public function checkHeaders()
	{
		foreach($this->assets as $asset)
		{
			if( ! $asset->exists())
			{
				throw new Exception(Craft::t($asset->filenamePath . ' could not be found.'));
			}
		}

		return $this;
	}

	/**
	 * Given an asset, fetches and returns minified contents.
	 *
	 * @param Minimee_AssetBaseModel $asset
	 * @return String
	 */
	public function minifyAsset($asset)
	{
		switch ($asset->type) :
			
			case 'js':

				craft()->minimee_helper->loadLibrary('jsmin');
				$contents = \JSMin::minify($asset->contents);

			break;
			
			case 'css':

				craft()->minimee_helper->loadLibrary('css_urirewriter');

				$cssPrependUrl = dirname($asset->filenameUrl) . '/';

				$contents = \Minify_CSS_UriRewriter::prepend($asset->contents, $cssPrependUrl);

				craft()->minimee_helper->loadLibrary('minify');
				$contents = \Minify_CSS::minify($contents);

			break;

		endswitch;

		return $contents;
	}

	/**
	 * Fetch or creates cache.
	 *
	 * @return String
	 */
	public function cache()
	{
		if( ! $this->cacheExists())
		{
			$this->createCache();
		}

		return $this->cacheFilenameUrl;
	}

	/**
	 * Creates cache of assets.
	 *
	 * @return Void
	 */
	public function createCache()
	{
		$contents = '';
		
		foreach($this->assets as $asset)
		{
			$contents .= craft()->minimee->minifyAsset($asset) . "\n";
		}

		IOHelper::writeToFile($this->cacheFilenamePath, $contents);

		$this->onCreateCache(new Event($this));
	}

	/**
	 * Raise our 'onCreateCache' event
	 *
	 * @return Void
	 */
	public function onCreateCache($event)
	{
		$this->raiseEvent('onCreateCache', $event);
	}

	/**
	 * Based on the cache's hash, attemtps to delete any older versions of same hash name.
	 */
	public function deleteExpiredCache()
	{
		$files = IOHelper::getFiles($this->settings->cachePath);

		foreach($files as $file)
		{
			// skip self
			if ($file === $this->cacheFilenamePath) continue;

			if (strpos($file, $this->cacheFilenameHashPath) === 0)
			{
				// suppress errors by passing true as second parameter
				IOHelper::deleteFile($file, true);
			}
		}
	}

	/**
	 * Checks if the cache exists.
	 *
	 * @return Bool
	 */
	public function cacheExists()
	{
		foreach ($this->assets as $asset)
		{
			$this->cacheFilenameTimestamp   = $asset->lastTimeModified;
			$this->cacheFilenameHash        = $asset->filename;
		}

		if( ! IOHelper::fileExists($this->cacheFilenamePath))
		{
			return false;
		}

		return true;
	}
}
