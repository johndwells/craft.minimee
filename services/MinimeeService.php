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
    public $settings;
    public $type;
    public $cachePath;
    public $cacheUrl;

    public function css($assets)
    {
        $assets = (is_array($assets)) ? $assets : array($assets);

        try
        {
            return $this->init()
                        ->setType('css')
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

    protected function _abort($e)
    {
        Craft::log($e, LogLevel::Warning);

        return false;
    }

    public function init()
    {
        $this->settings = craft()->plugins->getPlugin('minimee')->getSettings();
        $this->assets = null;
        $this->type = null;

        // TODO: better setting of path & URL
        $this->cachePath = $_SERVER['DOCUMENT_ROOT'] . '/' . $this->settings->cacheFolder;
        $this->cacheUrl = Craft::getSiteUrl() . $this->settings->cacheFolder;

        return $this;
    }

    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    public function setAssets($assets)
    {
        $this->assets = $assets;

        return $this;
    }

    public function flightcheck()
    {
        if($this->settings->disable)
        {
            throw new Exception(Craft::t('Disabled via config.'));
        }

        IOHelper::ensureFolderExists($this->cachePath);
        if( ! IOHelper::isWritable($this->cachePath))
        {
            throw new Exception(Craft::t('Cache folder is not writable: ' . $this->cachePath));
        }

        return $this;
    }

    public function checkHeaders()
    {
        // TODO: ensure each asset exists
        return $this;
    }

    public function cache()
    {
        // TODO: everything else!
        return false;
    }
}
