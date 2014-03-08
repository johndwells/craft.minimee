<?php
namespace Craft;

/**
 * Minimee by John D Wells
 *
 * @package   Minimee
 * @author    John D Wells
 * @copyright Copyright (c) 2012, John D Wells
 * @link      http://johndwells.com
 */

/**
 * Our plugin Service
 */
class MinimeeService extends BaseApplicationComponent
{
    protected $_assets                  = array();
    protected $_type                    = '';
    protected $_cacheFilename           = '';       // lastmodified value for cache
    protected $_cacheFilenameHash       = '';       // a hash of all asset filenames together
    protected $_cacheFilenameTimestamp  = '';       // eventual filename of cache
    protected $_settings                  = null;

    // --------------------------

    public function init()
    {
        // make sure the rest of the component initialises first
        parent::init();

        // immediately set our settings
        $this->setSettings(array());
    }

    public function getAssets()
    {
        return $this->_assets;
    }

    public function getCacheFilename()
    {
        if( ! $this->_cacheFilename)
        {
            $this->_cacheFilename = $this->cacheFilenameHash . '.' . $this->cacheFilenameTimestamp . '.' . $this->type;
        }

        return $this->_cacheFilename;
    }

    public function getCacheFilenameHash()
    {
        return sha1($this->_cacheFilenameHash);
    }

    public function getCacheFilenameHashPath()
    {
        return $this->settings->cachePath . $this->cacheFilenameHash;
    }

    public function getCacheFilenamePath()
    {
        return $this->settings->cachePath . $this->cacheFilename;
    }

    public function getCacheFilenameUrl()
    {
        return $this->settings->cacheUrl . $this->cacheFilename;
    }

    public function getCacheFilenameTimestamp()
    {
        return ($this->_cacheFilenameTimestamp == 0) ? '0000000000' : $this->_cacheFilenameTimestamp;
    }

    public function getSettings()
    {
        return $this->_settings;
    }

    public function getType()
    {
        return $this->_type;
    }

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

    public function setCacheFilenameTimestamp(DateTime $lastTimeModified)
    {
        $timestamp = $lastTimeModified->getTimestamp();
        $this->_cacheFilenameTimestamp = max($this->cacheFilenameTimestamp, $timestamp);
    }

    public function setCacheFilenameHash($name)
    {
        // remove any cache-busting strings so the cache name doesn't change with every edit.
        // format: .v.1330213450
        $this->_cacheFilenameHash .= preg_replace('/\.v\.(\d+)/i', '', $name);
    }

    /**
     * Configure our service based off the settings in plugin,
     * allowing plugin settings to be overridden at runtime.
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

    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    public function run($assets, $type)
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

    public function css($assets)
    {
        return $this->run($assets, 'css');
    }

    public function js($assets)
    {
        return $this->run($assets, 'js');
    }

    protected function _abort($e)
    {
        Craft::log($e, LogLevel::Warning);

        // re-throw the exception if in devMode
        if(craft()->config->get('devMode') && $this->settings->enabled)
        {
            throw new Exception($e);
        }

        return false;
    }

    public function reset()
    {
        $this->_assets                          = array();
        $this->_type                            = '';
        $this->_cacheFilename                   = '';
        $this->_cacheFilenameHash               = '';
        $this->_cacheFilenameTimestamp          = '';

        return $this;
    }

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

        IOHelper::ensureFolderExists($this->settings->cachePath);
        if( ! IOHelper::isWritable($this->settings->cachePath))
        {
            throw new Exception(Craft::t('Cache folder is not writable: ' . $this->settings->cachePath));
        }

        return $this;
    }

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

    public function cache()
    {
        if( ! $this->cacheExists())
        {
            $this->createCache();
        }

        return $this->cacheFilenameUrl;
    }

    public function createCache()
    {
        // the eventual contents of our cache
        $contents = '';
        
        foreach($this->assets as $asset)
        {
            $contents .= craft()->minimee->minifyAsset($asset) . "\n";
        }

        IOHelper::writeToFile($this->cacheFilenamePath, $contents);

        $this->cleanupCache();
    }

    public function cleanupCache()
    {
        // only run cleanup if in devmode...?
        if( ! craft()->config->get('devMode')) return;

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

    public function cacheExists()
    {
        // loop through our files once
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
