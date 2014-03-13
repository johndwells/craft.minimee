<?php namespace Craft;

use Twig_Markup;

/**
 * Minimee by John D Wells
 *
 * @author     	John D Wells <http://johndwells.com>
 * @package    	Minimee
 * @since		Craft 1.3
 * @copyright 	Copyright (c) 2014, John D Wells
 * @license 	http://opensource.org/licenses/mit-license.php MIT License
 * @link       	http://github.com/johndwells/Minimee-Craft
 */

/**
 * 
 */
class Minimee_HelperService extends BaseApplicationComponent
{
	/**
	 * Internal flag indicating if we've registered the Minify Loader class
	 */
	public static $registeredMinifyLoader;

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
	 *
	 * @param   string  Which type of tags to search for - CSS or JS
	 * @param   string  String to search
	 * @return  bool|array   Array of found matches, or false if none
	 */
	public function pregMatchAssetsByType($type, $haystack)
	{
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
	 * Loads our requested library
	 *
	 * On first call it will adjust the include_path, for Minify support
	 *
	 * @param   string  Name of library to require
	 * @return  void
	 */
	public function loadLibrary($which)
	{
		if( is_null(self::$registeredMinifyLoader))
		{
			// try to bump our memory limits for good measure
			@ini_set('memory_limit', '12M');
			@ini_set('memory_limit', '16M');
			@ini_set('memory_limit', '32M');
			@ini_set('memory_limit', '64M');
			@ini_set('memory_limit', '128M');
			@ini_set('memory_limit', '256M');

			require_once(CRAFT_PLUGINS_PATH . 'minimee/libraries/Minify/Loader.php');
			\Minify_Loader::register();

			self::$registeredMinifyLoader = true;
		}

		switch ($which) :

			case ('minify') :
				require_once(CRAFT_PLUGINS_PATH . 'minimee/libraries/Minify/CSS.php');
			break;

			case ('cssmin') :
				require_once(CRAFT_PLUGINS_PATH . 'minimee/libraries/CSSmin.php');
			break;
			
			case ('css_urirewriter') :
				require_once(CRAFT_PLUGINS_PATH . 'minimee/libraries/Minify/CSS/UriRewriter.php');
			break;

			case ('curl') :
				require_once(CRAFT_PLUGINS_PATH . 'minimee/libraries/EpiCurl.php');
			break;
			
			case ('jsmin') :
				require_once(CRAFT_PLUGINS_PATH . 'minimee/libraries/JSMin.php');
			break;
			
			case ('jsminplus') :
				require_once(CRAFT_PLUGINS_PATH . 'minimee/libraries/JSMinPlus.php');
			break;
			
			case ('html') :
				require_once(CRAFT_PLUGINS_PATH . 'minimee/libraries/Minify/HTML.php');
			break;

		endswitch;
	}
}