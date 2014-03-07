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