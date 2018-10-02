<?php
// Application middleware

// e.g: $app->add(new \Slim\Csrf\Guard);
$settings = $app->getContainer()->get('settings');

//$app->add(new Slim\Middleware\HttpBasicAuthentication(
//    $settings->get('auth')
//));
