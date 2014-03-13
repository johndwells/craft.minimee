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
	 * Shorthand function to process CSS
	 *
	 * @param Array $assets
	 * @param Array $settings
	 * @return String|Bool
	 */
	public function css($assets, $settings = array())
	{
		return $this->run('css', $assets, $settings);
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
	 * @param Array $settings
	 * @return String|Bool
	 */
	public function js($assets, $settings = array())
	{
		return $this->run('js', $assets, $settings);
	}

	/**
	 * Generate the HTML tag based on type
	 * In future this will be configurable.
	 *
	 * @param String $type
	 * @param Array $assets
	 * @return String
	 */
	public function makeTagsByType($type, $assets = array())
	{
		$assets = ( ! is_array($assets)) ? array($assets) : $assets;
		$tags = '';

		foreach($assets as $asset)
		{
			switch ($type)
			{
				case ('css') :

					$tags .= sprintf('<link rel="stylesheet" type="text/css" href="%s"/>', $asset);

				break;

				case ('js') :

					$tags .= sprintf('<script type="text/javascript" src="%s"></script>', $asset);

				break;
			}
		}

		return $tags;
	}

	/**
	 * Wrapper for how we must return a twig option rather than raw HTML
	 *
	 * @param string
	 * @return Twig_Markup
	 */
	public function returnHtmlAsTwigMarkup($html)
	{
		// Prevent having to use the |raw filter when calling variable in template
		// http://pastie.org/6412894#1
		$charset = craft()->templates->getTwig()->getCharset();
		return new Twig_Markup($html, $charset);
	}

	/**
	 * Main service function that encapsulates an entire Minimee run
	 *
	 * @param String $type
	 * @param Array $assets
	 * @param Array $settings
	 * @return String|Bool
	 */
	public function run($type, $assets, $settings = array())
	{
		$assets = (is_array($assets)) ? $assets : array($assets);
		$settings = (is_array($settings)) ? $settings : array($settings);

		try
		{
			return $this->reset()
						->setSettings($settings)
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
	 * Fetch or creates cache.
	 *
	 * @return String
	 */
	protected function cache()
	{
		if( ! $this->cacheExists())
		{
			$this->createCache();
		}

		return $this->getCacheUrl();
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

		if($this->settings->useResourceCache())
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
	 * Validate all assets prior to run.
	 *
	 * @return this
	 */
	protected function checkHeaders()
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
	 * Perform pre-flight checks to ensure we can run.
	 *
	 * @return this
	 */
	protected function flightcheck()
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
		
		if($this->settings->useResourceCache())
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
		if($this->settings->useResourceCache())
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
		if($this->settings->useResourceCache())
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
	 * Raise our 'onCreateCache' event
	 *
	 * @return Void
	 */
	protected function onCreateCache($event)
	{
		$this->raiseEvent('onCreateCache', $event);
	}

	/**
	 * Safely resets service to prepare for a clean run.
	 *
	 * @return this
	 */
	protected function reset()
	{
		$this->_assets                  = array();
		$this->_settings                = null;
		$this->_type                    = '';
		$this->_cacheHash               = '';
		$this->_cacheTimestamp          = '';

		return $this;
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
	 * Configure our service based off the settings in plugin,
	 * allowing plugin settings to be overridden at runtime.
	 *
	 * @param Array $settingsOverrides
	 * @return void
	 */
	protected function setSettings($settingsOverrides)
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
	protected function setType($type)
	{
		$this->type = $type;

		return $this;
	}
}
