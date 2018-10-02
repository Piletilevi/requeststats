<?php

use app\StatsController;
use Slim\Http\Request;
use Slim\Http\Response;

// Routes

$app->get('/syncdb', function (Request $request, Response $response, array $args) {
    $import = $this->get('stats_import');
    $import->run();
    $info = 'Imported ' . $import->getImportedRequests() . ' requests';
    $this->logger->info($info);
    return $response->write($info);
});

$app->get('/aggregate', function (Request $request, Response $response, array $args) {
    $import = $this->get('stats_aggregate');
    $import->run();
    $info = 'Aggregated ' . $import->getAggregatedRequests() . ' requests';
    $this->logger->info($info);
    return $response->write($info);
});

//$app->get('/[{name}]', function (Request $request, Response $response, array $args) {
//    // Sample log message
//    $this->logger->info("Slim-Skeleton '/' route");
//    return $this->view->render($response, 'index.twig', $args);
//});

$app->get('/[{mode}]', StatsController::class . ':index')->setName('stats');
