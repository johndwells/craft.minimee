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
	 * Inject into the template scope a read-only copy of Minimee's runtime settings
	 *
	 * @return Array
	 */
	public function getGlobals()
	{
		return array(
			'minimee' => minimee()->service->pluginSettings
		);
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
		$type = $this->detectType($html);
		if (!$type)
		{
			MinimeePlugin::log('Could not determine the type of asset to process.', LogLevel::Warning);
			return minimee()->service->makeTwigMarkupFromHtml($html);
		}

		// we need to find some assets in the HTML
		$assets = $this->pregMatchAssetsByType($type, $html);
		if (!$assets)
		{
			MinimeePlugin::log('No assets of type ' . $type . ' could be found.', LogLevel::Warning);
			return minimee()->service->makeTwigMarkupFromHtml($html);
		}

		// hand off the rest to our service
		$minified = minimee()->service->run($type, $assets, $settings);

		// false means we failed, so return original markup
		if (!$minified)
		{
			return minimee()->service->makeTwigMarkupFromHtml($html);
		}

		$minifiedAsTags = minimee()->service->makeTagsByType($type, $minified);

		// return minified tag(s) as Twig Markup
		return minimee()->service->makeTwigMarkupFromHtml($minifiedAsTags);
	}

	/**
	 * Quick string detection to determine type
	 *
	 * @param string
	 * @param bool|string
	 */
	protected function detectType($html = '')
	{
		if (strpos($html, '<link') !== FALSE)
		{
			return MinimeeType::Css;
		}

		if (strpos($html, '<script') !== FALSE)
		{
			return MinimeeType::Js;
		}

		return FALSE;
	}

	/**
	 * Helper function to parse content looking for CSS and JS tags.
	 * Returns array of links found.
	 *
	 * @param   string  Which type of tags to search for - CSS or JS
	 * @param   string  String to search
	 * @return  bool|array   Array of found matches, or false if none
	 */
	protected function pregMatchAssetsByType($type, $haystack)
	{
		switch (strtolower($type)) :

			case MinimeeType::Css :
				$pat = "/<link{1}.*?href=['|\"]{1}(.*?)['|\"]{1}[^>]*>/is";
			break;

			case MinimeeType::Js :
				$pat = "/<script{1}.*?src=['|\"]{1}(.*?)['|\"]{1}[^>]*>(.*?)<\/script>/is";
			break;

			default :
				return FALSE;

		endswitch;

		if (!preg_match_all($pat, $haystack, $matches, PREG_PATTERN_ORDER))
		{
			return FALSE;
		}

		return $matches[1];
	}
}
