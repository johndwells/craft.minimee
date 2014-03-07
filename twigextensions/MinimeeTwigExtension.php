<?php

namespace Craft;

class MinimeeTwigExtension extends \Twig_Extension
{
    public function getName()
    {
        return 'minimee';
    }

    public function getFilters()
    {
        return array('minimee' => new \Twig_Filter_Method($this, 'minimeeFilter'));
    }

    public function minimeeFilter($html, $settings = array())
    {
    	// we need a type to continue
    	$type = craft()->minimee_helper->detectType($html);
    	if( ! $type)
    	{
    		Craft::log('Could not determine the type of asset to process.', LogLevel::Warning);
    		return craft()->minimee_helper->returnHtmlAsTwigMarkup($html);
    	}

    	// we need to find some assets in the HTML
    	$assets = craft()->minimee_helper->pregMatchAssetsByType($html, $type);
    	if( ! $assets)
    	{
			Craft::log('No assets of type ' . $type . ' could be found.', LogLevel::Warning);
			return craft()->minimee_helper->returnHtmlAsTwigMarkup($html);
    	}

    	// hand off the rest to our service
        $settings = ( ! is_array($settings)) ? array($settings) : $settings;
		$minified = craft()->minimee->setConfig($settings)->$type($assets);

		// false means we failed, so return original markup
		if( ! $minified)
		{
			return craft()->minimee_helper->returnHtmlAsTwigMarkup($html);
		}

		// return minified tag(s) as Twig Markup
		return craft()->minimee_helper->returnHtmlAsTwigMarkup($minified);
    }
}
