<?php

namespace Craft;

use Twig_Markup;

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

    public function minimeeFilter($html, $type = false)
    {
    	// we need a type to continue
    	$type = ($type) ?: $this->_detectType($html);
    	if( ! $type)
    	{
    		Craft::log('Could not determine the type of asset to process.', LogLevel::Warning);
    		return $this->_returnTwigMarkup($html);
    	}

    	// we need to find some assets in the HTML
    	$assets = $this->_pregMatchAssetsByType($html, $type);
    	if( ! $assets)
    	{
			Craft::log('No assets of type ' . $type . ' could be found.', LogLevel::Warning);
			return $this->_returnTwigMarkup($html);
    	}

    	// hand off the rest to our service
		$minified = craft()->minimee->$type($assets);

		// false means we failed, so return original markup
		if( ! $minified)
		{
			return $this->_returnTwigMarkup($html);
		}

		// return minified tag(s) as Twig Markup
		return $this->_returnTwigMarkup($minified);
    }


    /**
     * Quick string detection to determine type
     *
     * @param string
     * @param bool|string
     */
    protected function _detectType($html = '')
    {
        if(strpos($html, '<link') !== FALSE)
        {
            return 'css';
        }

        if(strpos($html, '<script') !== FALSE)
        {
        	return 'js';
        }

        return FALSE;
    }


	/**
     * Helper function to parse content looking for CSS and JS tags.
     * Returns array of links found.
     * @param   string  String to search
     * @param   string  Which type of tags to search for - CSS or JS
     * @return  bool|array   Array of found matches, or false if none
     */
    protected function _pregMatchAssetsByType($haystack, $type)
    {
        // let's find the location of our cache files
        switch (strtolower($type)) :

            case 'css' :
                $pat = "/<link{1}.*?href=['|\"']{1}(.*?)['|\"]{1}[^>]*>/i";
            break;

            case 'js' :
                $pat = "/<script{1}.*?src=['|\"]{1}(.*?)['|\"]{1}[^>]*>(.*?)<\/script>/i";
            break;

            default :
                return FALSE;
            break;

        endswitch;

        if ( ! preg_match_all($pat, $haystack, $matches, PREG_PATTERN_ORDER))
        {
            return FALSE;
        }
        
        // free memory where possible
        unset($pat);

        return $matches;
    }


    /**
     * Wrapper for how we must return a twig option rather than raw HTML
     * @param string
     * @return Twig_Markup
     */
    protected function _returnTwigMarkup($html)
    {
    	// Prevent having to use the |raw filter when calling variable in template
    	// http://pastie.org/6412894#1
		$charset = craft()->templates->getTwig()->getCharset();
		return new Twig_Markup($html, $charset);
    }
}
