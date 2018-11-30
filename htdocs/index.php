<?php
if (PHP_SAPI == 'cli-server') {
    // To help the built-in PHP dev server, check if the request was actually for
    // something which should probably be served as a static file
    $url  = parse_url($_SERVER['REQUEST_URI']);
    $file = __DIR__ . $url['path'];
    if (is_file($file)) {
        return false;
    }
}

require __DIR__ . '/../vendor/autoload.php';

session_start();

// Instantiate the app
$settings = require __DIR__ . '/../src/settings.php';
$app = new \Slim\App($settings);

// Set up dependencies
require __DIR__ . '/../src/dependencies.php';

// Register middleware
require __DIR__ . '/../src/middleware.php';

// Register routes
require __DIR__ . '/../src/routes.php';

// Run app
$app->run();

echo '<div style="padding: 2em;">';
//echo $now = date('l d. F Y H:i') . '<br />';
echo $offset =   date('Z')/3600 . '<br />';
echo $diffGMT  =   date('P') . '<br />';
echo $summer   =   date("I") . '<br />'; //1, если дата соответствует летнему времени, 0 в противном случае.

/*
echo IntlDateFormatter::formatObject(
         new DateTime('2013-04-01 00:00:00 Europe/Moscow'),
         'LLLL',
         'ru-RU');
*/
$fmt = new IntlDateFormatter(
    '',

    IntlDateFormatter::FULL,
    IntlDateFormatter::LONG

 //   'Europe/Tallinn',
//    IntlDateFormatter::GREGORIAN
//    "m"
);
//$kuupaev		=	$fmt->format(new DateTime('2018-04 00:00:01 Europe/Tallinn'));
echo $kuupaev		=	$fmt->format(new DateTime());



var_dump(getdate()) ;
echo '</div>';