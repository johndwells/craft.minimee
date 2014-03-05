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
    public function getSettings()
    {
        return craft()->plugins->getPlugin('minimee')->getSettings();
    }
}
