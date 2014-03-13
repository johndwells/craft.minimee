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
	protected $_cacheHash       		= '';       // a hash of all asset filenames together
	protected $_cacheTimestamp  		= '';       // timestamp of cache
	protected $_settings                = null;		// instance of Minimee_SettingsModel


	/*================= PUBLIC METHODS ================= */


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

		return $this->getCacheUrl();
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
	 * Based on the cache's hash, attemtps to delete any older versions of same hash name.
	 */
	public function deleteExpiredCache()
	{
		$files = IOHelper::getFiles($this->settings->cachePath);

		foreach($files as $file)
		{
			// skip self
			if ($file === $this->cacheFilenamePath) continue;

			if (strpos($file, $this->cacheHashPath) === 0)
			{
				// suppress errors by passing true as second parameter
				IOHelper::deleteFile($file, true);
			}
		}
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
		
		if($this->settings->isResourceCache())
		{
			IOHelper::ensureFolderExists($this->settings->cachePath);
		}
		else
		{
			if( ! IOHelper::folderExists($this->settings->cachePath))
			{
				throw new Exception(Craft::t('Cache folder does not exist: ' . $this->settings->cachePath));
			}
		}

		if( ! IOHelper::isWritable($this->settings->cachePath))
		{
			throw new Exception(Craft::t('Cache folder is not writable: ' . $this->settings->cachePath));
		}

		return $this;
	}

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
	 * Raise our 'onCreateCache' event
	 *
	 * @return Void
	 */
	public function onCreateCache($event)
	{
		$this->raiseEvent('onCreateCache', $event);
	}

	/**
	 * Safely resets service to prepare for a clean run.
	 *
	 * @return this
	 */
	public function reset()
	{
		$this->_assets                  = array();
		$this->_type                    = '';
		$this->_cacheHash               = '';
		$this->_cacheTimestamp          = '';

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
			return $this->abort($e);
		}
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


	/*================= PROTECTED METHODS ================= */


	/**
	 * Internal function used when aborting due to error
	 *
	 * @param String $e
	 * @return Bool
	 */
	protected function abort($e)
	{
		Craft::log($e, LogLevel::Warning);

		if(craft()->config->get('devMode') && $this->settings->enabled)
		{
			throw new Exception($e);
		}

		return false;
	}

	/**
	 * Checks if the cache exists.
	 *
	 * @return Bool
	 */
	protected function cacheExists()
	{
		foreach ($this->assets as $asset)
		{
			$this->cacheTimestamp   = $asset->lastTimeModified;
			$this->cacheHash        = $asset->filename;
		}

		if( ! IOHelper::fileExists($this->cacheFilenamePath))
		{
			return false;
		}

		if($this->settings->isResourceCache())
		{
			$cacheLastTimeModified = IOHelper::getLastTimeModified($this->cacheFilenamePath);

			if($cacheLastTimeModified->getTimestamp() < $this->cacheTimestamp)
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Creates cache of assets.
	 *
	 * @return Void
	 */
	protected function createCache()
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
	 * Given an asset, fetches and returns minified contents.
	 *
	 * @param Minimee_AssetBaseModel $asset
	 * @return String
	 */
	protected function minifyAsset($asset)
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
	 * @return Array
	 */
	protected function getAssets()
	{
		return $this->_assets;
	}

	/**
	 * @return String
	 */
	protected function getCacheFilename()
	{
		if($this->settings->isResourceCache())
		{
			return sprintf('%s.%s', $this->cacheHash, $this->type);
		}

		return sprintf('%s.%s.%s', $this->cacheHash, $this->cacheTimestamp, $this->type);
	}

	/**
	 * @return String
	 */
	protected function getCacheFilenamePath()
	{
		return $this->settings->cachePath . $this->cacheFilename;
	}

	/**
	 * @return String
	 */
	protected function getCacheHash()
	{
		return sha1($this->_cacheHash);
	}

	/**
	 * @return String
	 */
	protected function getCacheHashPath()
	{
		return $this->settings->cachePath . $this->cacheHash;
	}

	/**
	 * @return String
	 */
	protected function getCacheTimestamp()
	{
		return ($this->_cacheTimestamp == 0) ? '0000000000' : $this->_cacheTimestamp;
	}

	/**
	 * @return String
	 */
	protected function getCacheUrl()
	{
		if($this->settings->isResourceCache())
		{
			$path = '/minimee/' . $this->cacheFilename;

			$dateParam = craft()->resources->dateParam;
			$params[$dateParam] = IOHelper::getLastTimeModified($this->cacheFilenamePath)->getTimestamp();

			return UrlHelper::getUrl(craft()->config->getResourceTrigger() . $path, $params);
		}
		
		return $this->settings->cacheUrl . $this->cacheFilename;
	}

	/**
	 * @return Minimee_SettingsModel
	 */
	protected function getSettings()
	{
		return $this->_settings;
	}

	/**
	 * @return String
	 */
	protected function getType()
	{
		return $this->_type;
	}

	/**
	 * @param Array $assets
	 * @return this
	 */
	protected function setAssets($assets)
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
	 * @param String $name
	 * @return Void
	 */
	protected function setCacheHash($name)
	{
		// remove any cache-busting strings so the cache name doesn't change with every edit.
		// format: .v.1330213450
		// this is held over from EE. Still a good idea to do something like this, perhaps improve in future.
		$this->_cacheHash .= preg_replace('/\.v\.(\d+)/i', '', $name);
	}

	/**
	 * @param DateTime $lastTimeModified
	 * @return Void
	 */
	protected function setCacheTimestamp(DateTime $lastTimeModified)
	{
		$timestamp = $lastTimeModified->getTimestamp();
		$this->_cacheTimestamp = max($this->cacheTimestamp, $timestamp);
	}

	/**
	 * @param String $type
	 * @return this
	 */
	protected function setType($type)
	{
		$this->type = $type;

		return $this;
	}
}
