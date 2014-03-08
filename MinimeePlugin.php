<?php

/**
 * Minimee by John D Wells
 *
 * @package   Minimee
 * @author    John D Wells
 * @copyright Copyright (c) 2012, John D Wells
 * @link      http://johndwells.com
 */

namespace Craft;

class MinimeePlugin extends BasePlugin
{
    public function getName()
    {
        return 'Minimee';
    }

    public function getVersion()
    {
        return '0.4.5';
    }

    public function getDeveloper()
    {
        return 'John D Wells';
    }

    public function getDeveloperUrl()
    {
        return 'http://johndwells.com';
    }

    public function hasCpSection()
    {
        return false;
    }

    public function defineSettings()
    {
        // use our settings model to define settings
        Craft::import('plugins.minimee.models.Minimee_SettingsModel');

        $settings = new Minimee_SettingsModel();

        return $settings->defineAttributes();
    }

    public function getSettingsHtml()
    {
        return craft()->templates->render('minimee/settings', array(
            'settings' => $this->getSettings()
        ));
    }

    public function addTwigExtension()
    {
        Craft::import('plugins.minimee.twigextensions.MinimeeTwigExtension');

        return new MinimeeTwigExtension();
    }

    public function getResourcePath($path)
    {
        // Are they requesting a drink image?
        if (strncmp($path, 'minimee/', 8) === 0)
        {
            return craft()->path->getStoragePath().'minimee/'.substr($path, 8);
        }
    }
}