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
	const TimestampZero         = '0000000000';         // exact string representation of "zero" timestamp
	const ResourceTrigger       = 'minimee';            // the trigger we use for our own resources

	protected $_assets          = array();              // array of Minimee_AssetModelInterface
	protected $_type            = '';                   // css or js
	protected $_cacheBase       = '';                   // a concat of all asset filenames together
	protected $_cacheTimestamp  = self::TimestampZero;  // max timestamp of all assets
	protected $_settings        = null;                 // instance of Minimee_SettingsModel

	protected static $_pluginSettings	= array();		// static array of settings, a merge of DB and filesystem settings


	/*================= PUBLIC METHODS ================= */


	/**
	 * Based on the cache's hashed base, attempts to delete any older versions of same name.
	 */
	public function deleteExpiredCache()
	{
		MinimeePlugin::log(Craft::t('Minimee is attempting to delete expired caches.'));

		$files = IOHelper::getFiles($this->settings->cachePath);

		foreach($files as $file)
		{
			// skip self
			if ($file === $this->makePathToCacheFilename()) continue;

			if (strpos($file, $this->makePathToHashOfCacheBase()) === 0)
			{
				MinimeePlugin::log(Craft::t('Minimee is attempting to delete file: ') . $file);

				// suppress errors by passing true as second parameter
				IOHelper::deleteFile($file, true);
			}
		}
	}

	/**
	 * During startup, fetch settings from our plugin / config
	 *
	 * @return Void
	 */
	public function init()
	{
		parent::init();

		$this->initPluginSettings();
	}

	/**
	 * Generate the HTML tag based on type
	 *
	 * @param String $type
	 * @param Array $assets
	 * @return String
	 */
	public function makeTagsByType($type, $assets = array())
	{
		$assets = ( ! is_array($assets)) ? array($assets) : $assets;
		$tags = '';

		switch ($type)
		{
			case (MinimeeType::Css) :

				$settingsName = 'cssReturnTemplate';

			break;

			case (MinimeeType::Js) :

				$settingsName = 'jsReturnTemplate';

			break;
		}

		foreach($assets as $asset)
		{
			$tags .= sprintf($this->settings->$settingsName, $asset);
		}

		return $tags;
	}

	/**
	 * Wrapper for how we must return a twig option rather than raw HTML
	 *
	 * @param string
	 * @return Twig_Markup
	 */
	public function makeTwigMarkupFromHtml($html)
	{
		// Prevent having to use the |raw filter when calling variable in template
		// http://pastie.org/6412894#1
		$charset = craft()->templates->getTwig()->getCharset();
		return new \Twig_Markup($html, $charset);
	}

	/**
	 * Main service function that encapsulates an entire Minimee run
	 *
	 * @param String $type
	 * @param Array $assets
	 * @param Array $settings
	 * @return Array|Bool
	 */
	public function run($type, $assets, $settings = array())
	{
		$assets = ( ! is_array($assets)) ? array($assets) : $assets;
		$settings = ( ! is_array($settings)) ? array($settings) : $settings;

		try
		{
			$this->reset()
				 ->setRuntimeSettings($settings)
				 ->setType($type)
				 ->setAssets($assets)
				 ->flightcheck()
				 ->checkHeaders();

			$return = array();
			if($this->isCombineEnabled())
			{
				$return[] = $this->ensureCacheExists()
								 ->makeReturn();
			}
			else
			{
				foreach($assets as $asset)
				{
					$return[] = $this->reset()
									 ->setRuntimeSettings($settings)
									 ->setType($type)
									 ->setAssets($asset)
									 ->ensureCacheExists()
									 ->makeReturn();
				}
			}
		}
		catch (Exception $e)
		{
			return $this->abort($e);
		}

		return $return;
	}


	/*================= PROTECTED METHODS ================= */


	/**
	 * Internal function used when aborting due to error
	 *
	 * @param String $e
	 * @param String $level
	 * @return Bool
	 */
	protected function abort($e, $level = LogLevel::Error)
	{
		MinimeePlugin::log(Craft::t('Minimee is aborting with the message: ') . $e, $level);

		if(craft()->config->get('devMode')
			&& $this->settings->enabled
			&& ($level == LogLevel::Warning || $level == LogLevel::Error))
		{
			throw new Exception($e);
		}

		return false;
	}

	/**
	 * Append an asset's name to the cacheBase
	 * @param String $name
	 * @return Void
	 */
	protected function appendToCacheBase($name)
	{
		$this->_cacheBase .= $name;
	}

	/**
	 * Fetch or creates cache.
	 *
	 * @return String
	 */
	protected function ensureCacheExists()
	{
		if( ! $this->cacheExists())
		{
			if( ! $this->createCache())
			{
				throw new Exception(Craft::t('Minimee could not find asset ' . $asset->filenamePath . '.'));
			}
		}

		return $this;
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
			$this->setMaxCacheTimestamp($asset->lastTimeModified);
			$this->appendToCacheBase($asset->filename);
		}

		if( ! IOHelper::fileExists($this->makePathToCacheFilename()))
		{
			return false;
		}

		if($this->settings->useResourceCache())
		{
			$cacheLastTimeModified = IOHelper::getLastTimeModified($this->makePathToCacheFilename());

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
				throw new Exception(Craft::t('Minimee could not find asset ' . $asset->filenamePath . '.'));
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
			$contents .= $this->minifyAsset($asset) . "\n";
		}

		if( ! IOHelper::writeToFile($this->makePathToCacheFilename(), $contents))
		{
			return false;
		}

		$this->onCreateCache(new Event($this));

		return true;
	}

	/**
	 * set plugin / config settings
	 *
	 * @return Void
	 */
	protected function setPluginSettings($settings = array())
	{
		self::$_pluginSettings = $settings;
	}

	/**
	 * get our plugin / config settings
	 *
	 * @return Array
	 */
	protected function getPluginSettings()
	{
		if( ! self::$_pluginSettings)
		{
			$this->initPluginSettings();
		}

		return self::$_pluginSettings;
	}
	
	/**
	 * Fetch settings from our plugin / config
	 *
	 * @return Void
	 */
	protected function initPluginSettings()
	{
		$settings = minimee()->plugin->getSettings()->getAttributes();

		// as of v2.0 we can take filesystem configs
		if(version_compare('2.0', craft()->getVersion(), '<='))
		{
			$settings = $this->supportLegacyNamesFromConfig($settings);

			foreach($settings as $attribute => $value)
			{
				if(craft()->config->exists($attribute, 'minimee'))
				{
					$settings[$attribute] = craft()->config->get($attribute, 'minimee');
				}
			}
		}

		$this->setPluginSettings($settings);

		MinimeePlugin::log(Craft::t('Minimee has been initialised.'));
	}

	/**
	 * Return whether we should combine our cache or not
	 *
	 * @return Bool
	 */
	protected function isCombineEnabled()
	{
		switch($this->type) :
			case MinimeeType::Css :
				$isCombineEnabled = (bool) $this->settings->combineCssEnabled;
			break;

			case MinimeeType::Js :
				$isCombineEnabled = (bool) $this->settings->combineJsEnabled;
			break;
		endswitch;

		return $isCombineEnabled;
	}

	/**
	 * Determine if string is valid URL
	 *
	 * @param   string  String to test
	 * @return  bool    TRUE if yes, FALSE if no
	 */
	protected function isUrl($string)
	{
		// from old _isURL() file from Carabiner Asset Management Library
		// modified to support leading with double slashes
		return (preg_match('@((https?:)?//([-\w\.]+)+(:\d+)?(/([\w/_\.]*(\?\S+)?)?)?)@', $string) > 0);
	}

	/**
	 * Perform pre-flight checks to ensure we can run.
	 *
	 * @return this
	 */
	protected function flightcheck()
	{
		if ( ! self::$_pluginSettings)
		{
			throw new Exception(Craft::t('Minimee is not installed.'));
		}

		if( ! $this->settings->enabled)
		{
			throw new Exception(Craft::t('Minimee has been disabled via settings.'));
		}

		if( ! $this->settings->validate())
		{
			$exceptionErrors = '';
			foreach($this->settings->getErrors() as $error)
			{
				$exceptionErrors .= implode('. ', $error);
			}

			throw new Exception(Craft::t('Minimee has detected invalid plugin settings: ') . $exceptionErrors);
		}
		
		if($this->settings->useResourceCache())
		{
			IOHelper::ensureFolderExists($this->makePathToStorageFolder());
		}
		else
		{
			if( ! IOHelper::folderExists($this->settings->cachePath))
			{
				throw new Exception(Craft::t('Minimee\'s Cache Folder does not exist: ' . $this->settings->cachePath));
			}

			if( ! IOHelper::isWritable($this->settings->cachePath))
			{
				throw new Exception(Craft::t('Minimee\'s Cache Folder is not writable: ' . $this->settings->cachePath));
			}
		}

		if( ! $this->assets)
		{
			throw new Exception(Craft::t('Minimee has no assets to operate upon.'));
		}

		if( ! $this->type)
		{
			throw new Exception(Craft::t('Minimee has no value for `type`.'));
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
	 * @return Array
	 */
	protected function getCacheBase()
	{
		return $this->_cacheBase;
	}

	/**
	 * @return String
	 */
	protected function getCacheTimestamp()
	{
		return ($this->_cacheTimestamp) ? $this->_cacheTimestamp : self::TimestampZero;
	}

	/**
	 * @return Minimee_SettingsModel
	 */
	protected function getSettings()
	{
		// if null, then set to an empty array
		if(is_null($this->_settings))
		{
			$this->setRuntimeSettings(array());
		}

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
	 * Returns a complete cache filename.
	 * If a "Resource" cache, format is:
	 *		hashOfCacheBase.type
	 *		e.g. asdf1234.css
	 * Otherwise, format is:
	 *		hashOfCacheBase.timestamp.type
	 *		e.g. asdf1234.12345678.css
	 *
	 * @return String
	 */
	protected function makeCacheFilename()
	{
		if($this->settings->useResourceCache())
		{
			return sprintf('%s.%s', $this->makeHashOfCacheBase(), $this->type);
		}

		return sprintf('%s.%s.%s', $this->makeHashOfCacheBase(), $this->cacheTimestamp, $this->type);
	}

	/**
	 * @return String
	 */
	protected function makeHashOfCacheBase()
	{
		return sha1($this->_cacheBase);
	}

	/**
	 * @return String
	 */
	protected function makePathToCacheFilename()
	{
		if($this->settings->useResourceCache())
		{
			return craft()->path->getStoragePath() . self::ResourceTrigger . '/' . $this->makeCacheFilename();
		}

		return $this->settings->cachePath . $this->makeCacheFilename();
	}

	/**
	 * @return String
	 */
	protected function makePathToHashOfCacheBase()
	{
		if($this->settings->useResourceCache())
		{
			return $this->makePathToStorageFolder() . $this->makeHashOfCacheBase();
		}

		return $this->settings->cachePath . $this->makeHashOfCacheBase();
	}

	/**
	 * @return String
	 */
	public function makePathToStorageFolder()
	{
		return craft()->path->getStoragePath() . self::ResourceTrigger . '/';
	}

	/**
	 * @return String
	 */
	protected function makeReturn()
	{
		if($this->settings->getReturnType() == 'contents')
		{
			return IOHelper::getFileContents($this->makePathToCacheFilename());
		}
		else
		{
			return $this->makeUrlToCacheFilename();
		}
	}

	/**
	 * @return String
	 */
	protected function makeUrlToCacheFilename()
	{
		if($this->settings->useResourceCache())
		{
			$path = '/' . self::ResourceTrigger . '/' . $this->makeCacheFilename();

			$dateParam = craft()->resources->dateParam;
			$params[$dateParam] = IOHelper::getLastTimeModified($this->makePathToCacheFilename())->getTimestamp();

			return UrlHelper::getUrl(craft()->config->getResourceTrigger() . $path, $params);
		}
		
		return $this->settings->cacheUrl . $this->makeCacheFilename();
	}

	/**
	 * Given an asset, fetches and returns minified contents.
	 *
	 * @param Minimee_BaseAssetModel $asset
	 * @return String
	 */
	protected function minifyAsset($asset)
	{
		craft()->config->maxPowerCaptain();

		switch ($this->type) :
			
			case MinimeeType::Js:

				if($this->settings->minifyJsEnabled)
				{
					$contents = \JSMin::minify($asset->contents);
				}
				else
				{
					$contents = $asset->contents;
				}

				// Play nice with others by ensuring a semicolon at eof
				if(substr($contents, -1) != ';')
				{
					$contents .= ';';
				}

			break;
			
			case MinimeeType::Css:

				$cssPrependUrl = dirname($asset->filenameUrl) . '/';

				$contents = \Minify_CSS_UriRewriter::prepend($asset->contents, $cssPrependUrl);

				if($this->settings->minifyCssEnabled)
				{
					$contents = \Minify_CSS::minify($contents);
				}

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
		$this->_cacheBase               = '';
		$this->_cacheTimestamp          = self::TimestampZero;
		$this->_settings                = null;
		$this->_type                    = '';

		return $this;
	}

	/**
	 * @param Array $assets
	 * @return this
	 */
	protected function setAssets($assets)
	{
		$assets = ( ! is_array($assets)) ? array($assets) : $assets;

		foreach($assets as $asset)
		{
			if ($this->isUrl($asset))
			{
				$model = array(
					'filename' => $asset,
					'filenameUrl' => $asset,
					'filenamePath' => $asset
				);

				$this->_assets[] = minimee()->makeRemoteAssetModel($model);
			}
			else
			{
				$model = array(
					'filename' => $asset,
					'filenameUrl' => $this->settings->baseUrl . $asset,
					'filenamePath' => $this->settings->filesystemPath . $asset
				);

				$this->_assets[] = minimee()->makeLocalAssetModel($model);
			}
		}

		return $this;
	}

	/**
	 * @param String $name
	 * @return Void
	 */
	protected function setCacheBase($name)
	{
		$this->_cacheBase = $name;

		return $this;
	}

	/**
	 * @param String $dateTime
	 * @return Void
	 */
	protected function setCacheTimestamp($timestamp)
	{
		$this->_cacheTimestamp = $timestamp ?: self::TimestampZero;

		return $this;
	}

	/**
	 * @param DateTime $lastTimeModified
	 * @return Void
	 */
	protected function setMaxCacheTimestamp(DateTime $lastTimeModified)
	{
		$timestamp = $lastTimeModified->getTimestamp();
		$this->cacheTimestamp = max($this->cacheTimestamp, $timestamp);

		return $this;
	}

	/**
	 * Configure our service based off the settings in plugin,
	 * allowing plugin settings to be overridden at runtime.
	 *
	 * @param Array $settingsOverrides
	 * @return void
	 */
	protected function setRuntimeSettings($settingsOverrides)
	{
		$settingsOverrides = ( ! is_array($settingsOverrides)) ? array($settingsOverrides) : $settingsOverrides;

		$settingsOverrides = $this->supportLegacyNamesAtRuntime($settingsOverrides);

		$runtimeSettings = array_merge($this->getPluginSettings(), $settingsOverrides);

		$this->settings = minimee()->makeSettingsModel($runtimeSettings);

		return $this;
	}

	/**
	 * Manually pass in an instance of Minimee_ISettingsModel.
	 *
	 * @param Craft\Minimee_ISettingsModel $settings
	 * @return void
	 */
	protected function setSettings(Minimee_ISettingsModel $settings)
	{
		$this->_settings = $settings;

		return $this;
	}

	/**
	 * @param String $type
	 * @return this
	 */
	protected function setType($type)
	{
		if($type !== MinimeeType::Css && $type !== MinimeeType::Js)
		{
			throw new Exception(Craft::t('Attempting to set an unknown type `' . $type . '`.'));
		}

		$this->_type = $type;

		return $this;
	}

	/**
	 * Handle backwards-compat for 'cssTagTemplate' and 'jsTagTemplate' setting names.
	 * Remove in 1.x release!
	 *
	 * @param Array $settings
	 * @return Array
	 */
	protected function supportLegacyNamesFromConfig($settings = array())
	{
		$settingNameMap = array(
			'cssTagTemplate' => 'cssReturnTemplate',
			'jsTagTemplate' => 'jsReturnTemplate');

		foreach($settingNameMap as $oldAttributeName => $newAttributeName)
		{
			if(craft()->config->exists($oldAttributeName, 'minimee'))
			{
				$settings[$newAttributeName] = craft()->config->get($oldAttributeName, 'minimee');
			}
		}

		return $settings;
	}

	/**
	 * Handle backwards-compat for 'cssTagTemplate' and 'jsTagTemplate' setting names.
	 * Remove in 1.x release!
	 *
	 * @param Array $runtimeSettings
	 * @return Array
	 */
	protected function supportLegacyNamesAtRuntime($runtimeSettings = array())
	{
		$settingNameMap = array(
			'cssTagTemplate' => 'cssReturnTemplate',
			'jsTagTemplate' => 'jsReturnTemplate');

		foreach($settingNameMap as $oldAttributeName => $newAttributeName)
		{
			if(array_key_exists($oldAttributeName, $runtimeSettings))
			{
				$runtimeSettings[$newAttributeName] = $runtimeSettings[$oldAttributeName];
				unset($runtimeSettings[oldAttributeName]);
			}
		}

		return $runtimeSettings;
	}
}
