<?php namespace Craft;

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
class MinimeeTwigExtension extends \Twig_Extension
{
	/**
	 * Name of our twig filter
	 *
	 * @return String
	 */
	public function getName()
	{
		return 'minimee';
	}

	/**
	 * Return an array of twig filters
	 *
	 * @return Array
	 */
	public function getFilters()
	{
		return array('minimee' => new \Twig_Filter_Method($this, 'minimeeFilter'));
	}

	/**
	 * Define our filter
	 * 
	 * @param String $html
	 * @param Array $settings
	 * @return String
	 */
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
		$assets = craft()->minimee_helper->pregMatchAssetsByType($type, $html);
		if( ! $assets)
		{
			Craft::log('No assets of type ' . $type . ' could be found.', LogLevel::Warning);
			return craft()->minimee_helper->returnHtmlAsTwigMarkup($html);
		}

		// hand off the rest to our service
		$minified = craft()->minimee->$type($assets, $settings);

		// false means we failed, so return original markup
		if( ! $minified)
		{
			return craft()->minimee_helper->returnHtmlAsTwigMarkup($html);
		}

		$minifiedAsTags = craft()->minimee->makeTagsByType($type, $minified);

		// return minified tag(s) as Twig Markup
		return craft()->minimee_helper->returnHtmlAsTwigMarkup($minifiedAsTags);
	}
}
