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
        return '1.0';
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
        return array(
            'cacheFolder'              => AttributeType::String,
            'disable'                  => AttributeType::Bool
        );
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
}