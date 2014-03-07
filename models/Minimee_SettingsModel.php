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
 * 
 */
class Minimee_SettingsModel extends BaseModel
{
	/*
	 * These are internal attributes only, not defined by Minimee_SettingsModel::defineAttributes()
	 * They are read-only, accessiable via magic getters e.g. $settings->cachePath
	 */
    private $_cachePath;
    private $_cacheUrl;
    private $_basePath;
    private $_baseUrl;

    /**
     *
     * @return string
     */
    public function __toString()
    {
        return (bool) $this->enabled;
    }

    // --------------------

    /**
     * @return Array
     */
    public function defineAttributes()
    {
        return array(
            'cacheFolder'   => AttributeType::String,
            'enabled'       => AttributeType::Bool,
            // 'remoteMode'    => array(AttributeType::Enum, 'values' => "fgc,curl")
        );
    }

    // --------------------

    /**
     *
     * @static
     * @param mixed $attributes
     * @return UserModel
     */
    public static function populateModel($attributes)
    {
        $settings = parent::populateModel($attributes);

        // sanitise our cache folder
        $settings->cacheFolder = trim($settings->cacheFolder, " /\\");

        return $settings;
    }

    // --------------------

    public function getCachePath()
    {
        if($this->_cachePath === null)
        {
            if ($this->cacheFolder != '')
            {
                $this->_cachePath = $this->basePath . '/' . $this->cacheFolder . '/';
            }
            else
            {
                $this->_cachePath = craft()->path->getStoragePath() . 'minimee/';
            }
        }

        return $this->_cachePath;
    }
    
    public function getCacheUrl()
    {
        if($this->_cacheUrl === null)
        {
            if ($this->cacheFolder != '')
            {
                $this->_cacheUrl = $this->baseUrl . '/' . $this->cacheFolder . '/';
            }
            else
            {
                $this->_cacheUrl = UrlHelper::getResourceUrl('minimee') . '/';
            }
        }

        return $this->_cacheUrl;
    }
    
    public function getBaseUrl()
    {
        if($this->_baseUrl === null)
        {
            $this->_baseUrl = craft()->getSiteUrl();
        }

        return $this->_baseUrl;
    }
    
    public function getBasePath()
    {
        if($this->_basePath === null)
        {
            $this->_basePath = $_SERVER['DOCUMENT_ROOT'] . '/';
        }

        return $this->_basePath;
    }

    public function getRemoteMode()
    {
        return 'fgc';
    }

    /**
     * @return Bool whether cache folder exists
     */
    public function exists()
    {
        return IOHelper::folderExists($this->cachePath);
    }

    // --------------------

}