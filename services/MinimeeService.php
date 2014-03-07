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
    public $assets;
    public $type;

    // read-only config
    private $_config;

    // --------------------------

    public function init()
    {
        // make sure the rest of the component initialises first
        parent::init();

        // configure our service based off the settings in plugin
        $plugin = craft()->plugins->getPlugin('minimee');

        // this will only be done once
        $this->_config = Minimee_ConfigModel::populateModel($plugin->getSettings());
    }

    /**
     * Read-Only config
     */
    public function getConfig()
    {
        return $this->_config;
    }

    // --------------------------

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
        if(craft()->config->get('devMode'))
        {
            throw new Exception($e);
        }

        return false;
    }

    public function reset()
    {
        $this->assets = null;
        $this->type = null;

        return $this;
    }

    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    public function setAssets($assets)
    {
        foreach($assets as $asset)
        {
            if (craft()->minimee_helper->isUrl($asset))
            {
                $model = array(
                    'filename' => $asset
                );

                $this->assets[] = Minimee_RemoteAssetModel::populateModel($model);
            }
            else
            {
                $model = array(
                    'filename' => $this->config->basePath . $asset
                );

                $this->assets[] = Minimee_LocalAssetModel::populateModel($model);
            }
        }

        return $this;
    }

    public function flightcheck()
    {
        if ($this->config === null)
        {
            throw new Exception(Craft::t('Not installed.'));
        }

        if($this->config->disable)
        {
            throw new Exception(Craft::t('Disabled via config.'));
        }

        IOHelper::ensureFolderExists($this->config->cachePath);
        if( ! IOHelper::isWritable($this->config->cachePath))
        {
            throw new Exception(Craft::t('Cache folder is not writable: ' . $this->config->cachePath));
        }

        return $this;
    }

    public function checkHeaders()
    {
        // TODO: ensure each asset exists
        foreach($this->assets as $asset)
        {
            if( ! $asset->exists())
            {
                throw new Exception(Craft::t($asset->filename . ' could not be found.'));
            }
        }

        return $this;
    }

    public function minify($asset)
    {
        return $asset->contents;
    }

    public function cache()
    {
        $cache = Minimee_CacheModel::populateModel(array(
            'assets' => $this->assets,
            'type' => $this->type,
            'cachePath' => $this->config->cachePath,
            'cacheUrl' => $this->config->cacheUrl)
        );

        if( ! $cache->exists())
        {
            return $cache->create();
        }

        return $this->config->cacheUrl . $cache->filename;
    }


}
