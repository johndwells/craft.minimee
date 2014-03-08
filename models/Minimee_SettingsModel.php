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
            'cacheFolder'       => AttributeType::String,
            'enabled'           => array(AttributeType::Bool,'default' => true),
            'filesystemPath'    => AttributeType::String,
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
        if ($this->cacheFolder != '')
        {
            return $this->filesystemPath . '/' . $this->cacheFolder . '/';
        }
        else
        {
            return craft()->path->getStoragePath() . 'minimee/';
        }
    }
    
    public function getCacheUrl()
    {
        if ($this->cacheFolder != '')
        {
            return $this->baseUrl . $this->cacheFolder . '/';
        }
        else
        {
            return UrlHelper::getResourceUrl('minimee') . '/';
        }
    }
    
    public function getBaseUrl()
    {
        return craft()->getSiteUrl();
    }

    public function getRemoteMode()
    {
        return 'fgc';
    }

    public function setAttribute($name, $value)
    {
        if($name == 'filesystemPath')
        {
            $value = ($value) ? craft()->config->parseEnvironmentString($value) : $_SERVER['DOCUMENT_ROOT'] . '/';
        }

        parent::setAttribute($name, $value);
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