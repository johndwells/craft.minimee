<?php
namespace Craft;

use Twig_Markup;

/**
 * Minimee by John D Wells
 *
 * @package   Minimee
 * @author    John D Wells
 * @copyright Copyright (c) 2012, John D Wells
 * @link      http://johndwells.com
 */

/**
 * A helper service for our plugin
 */
class Minimee_HelperService extends BaseApplicationComponent
{
	/**
     * Determine if string is valid URL
     *
     * @param   string  String to test
     * @return  bool    TRUE if yes, FALSE if no
     */
    public function isUrl($string)
    {
        // from old _isURL() file from Carabiner Asset Management Library
        // modified to support leading with double slashes
        return (preg_match('@((https?:)?//([-\w\.]+)+(:\d+)?(/([\w/_\.]*(\?\S+)?)?)?)@', $string) > 0);
    }
    // ------------------------------------------------------

	/**
     * Helper function to parse content looking for CSS and JS tags.
     * Returns array of links found.
     * @param   string  String to search
     * @param   string  Which type of tags to search for - CSS or JS
     * @return  bool|array   Array of found matches, or false if none
     */
    public function pregMatchAssetsByType($haystack, $type)
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

        return $matches[1];
    }


    /**
     * Quick string detection to determine type
     *
     * @param string
     * @param bool|string
     */
    public function detectType($html = '')
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
     * Wrapper for how we must return a twig option rather than raw HTML
     * @param string
     * @return Twig_Markup
     */
    public function returnHtmlAsTwigMarkup($html)
    {
    	// Prevent having to use the |raw filter when calling variable in template
    	// http://pastie.org/6412894#1
		$charset = craft()->templates->getTwig()->getCharset();
		return new Twig_Markup($html, $charset);
    }

}