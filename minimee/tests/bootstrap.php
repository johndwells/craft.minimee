<?php

// our tests use this
require_once __DIR__ . '/vendor/autoload.php';

// Craft's own bootstrap here
require_once '/Users/John/Sites/craft.dev/craft/app/tests/bootstrap.php';

// We are unable to autoload this for some reason...
// I've traced it to the fact that it extends BaseTest
// I'm unclear why that should be a problem though?
require_once __DIR__ . '/MinimeeBaseTest.php';

// the first time we run phpunit we always seem to get an initial error thrown that this does not exist
$_SERVER['SERVER_SOFTWARE'] = 'Apache';

// this usually happens in MinimeePlugin::init()
require_once __DIR__ . '/../library/vendor/autoload.php';
