<?php

namespace Craft;

class MinimeeVariable
{
	public function css($assets)
    {
        return $this->_run($assets, 'css');
    }

    public function js($assets)
    {
        return $this->_run($assets, 'js');
    }

    protected function _run($assets, $type)
    {
		$minified = craft()->minimee->$type($assets);

		// false means we failed, so return original markup
		if( ! $minified)
		{
			return craft()->minimee_helper->returnHtmlAsTwigMarkup($html);
		}

		// return minified tag(s) as Twig Markup
		return craft()->minimee_helper->returnHtmlAsTwigMarkup($minified);

    }
}