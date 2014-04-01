<?php

/**
 * As of Craft 2.0, Minimee supports the ability to override
 * the CP Settings with filesystem configs.
 * Note that this does NOT reduce any processing/DB overheads,
 * but it may suit how you prefer to configure Minimee across
 * multiple environments.
 *
 * To use this feature, begin by copying the contents of this file
 * into a file named `minimee.php`, and move it to your
 * craft/app/config folder. Then uncomment and set as few or
 * as many settings as you wish.
 *
 * For more on
 * how multi-environment configs work in Craft, see
 * http://buildwithcraft.com/docs/multi-environment-configs.
 */

return array(
	/**
	 * All the environments...
	 */
	'*' => array(

		/**
		 * Is Minimee enabled?
		 * default: true
		 */
		// 'enabled' => true,

		/**
		 * Combine CSS assets to a single cache?
		 * default: true
		 */
		// 'combineCssEnabled' => true,

		/**
		 * Combine JS assets to a single cache?
		 * default: true
		 */
		// 'combineJsEnabled' => true,

		/**
		 * Minify CSS assets?
		 * default: true
		 */
		// 'minifyCssEnabled' => true,

		/**
		 * Minify JS assets?
		 * default: true
		 */
		// 'minifyJsEnabled' => true,

		/**
		 * The template to use when embedding the cached CSS tag
		 * default: <link rel="stylesheet" href="%s"/>
		 */
		// 'cssTagTemplate' => '',

		/**
		 * The template to use when embedding the cached JS tag
		 * default: <script src="%s"></script>
		 */
		// 'jsTagTemplate' => '',

		/**
		 * The Filesystem Path to your cache folder
		 * default: $_SERVER['DOCUMENT_ROOT']
		 */
		// 'filesystemPath' => '',

		/**
		 * The Filesystem Path to your cache folder
		 * default: Craft's Storage Folder, e.g. craft/storage/minimee
		 */
		// 'cachePath' => '',

		/**
		 * The URL to your cache folder
		 * default: Craft Resource URL, e.g. http://domain.com/resources/minimee
		 */
		// 'cacheUrl' => ''
	)
);