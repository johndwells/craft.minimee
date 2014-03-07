<?php

namespace Craft;

class MinimeeVariable
{
	public function css($assets, $settings = array())
    {
        return $this->_run($assets, 'css', $settings);
    }

    public function js($assets, $settings = array())
    {
        return $this->_run($assets, 'js', $settings);
    }

    protected function _run($assets, $type, $settings = array())
    {
		$minified = craft()->minimee->setSettings($settings)->$type($assets);

		// false means we failed, so return original markup
		if( ! $minified)
		{
            $html = craft()->minimee_helper->makeTagsByType($assets, $type);

			return craft()->minimee_helper->returnHtmlAsTwigMarkup($html);
		}

        $html = craft()->minimee_helper->makeTagsByType($minified, $type);

		// return minified tag(s) as Twig Markup
		return craft()->minimee_helper->returnHtmlAsTwigMarkup($html);

    }
}