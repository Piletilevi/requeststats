<?php
// DIC configuration

$container = $app->getContainer();

$container['view'] = function ($c) {
    $settings = $c->get('settings')['renderer'];
    $view = new \Slim\Views\Twig($settings['template_path'], [
        // https://twig.symfony.com/doc/2.x/api.html#environment-options
        'cache' => $settings['template_cache_path'],
        'auto_reload' => $settings['auto_reload'],
    ]);

    // Instantiate and add Slim specific extension
    $basePath = rtrim(str_ireplace('index.php', '', $c['request']->getUri()->getBasePath()), '/');
    $view->addExtension(new Slim\Views\TwigExtension($c['router'], $basePath));

    return $view;
};

$container['errorHandler'] = function ($c) {
    return function (
        \Psr\Http\Message\ServerRequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        \Exception $exception
    ) use ($c) {
        $c->get('logger')->error($exception->getMessage());
        return call_user_func_array(new Slim\Handlers\Error($c->get('settings')['displayErrorDetails']), [
            $request,
            $response,
            $exception
        ]);
    };
};

$container['phpErrorHandler'] = function ($c) {
    return function (
        \Psr\Http\Message\ServerRequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        \Throwable $exception
    ) use ($c) {
        $c->get('logger')->error($exception->getMessage());
        return call_user_func_array(new Slim\Handlers\PhpError($c->get('settings')['displayErrorDetails']), [
            $request,
            $response,
            $exception
        ]);
    };
};

$container['renderer'] = function ($c) {
    $settings = $c->get('settings')['renderer'];
    return new Slim\Views\PhpRenderer($settings['template_path']);
};

$container['logger'] = function ($c) {
    $settings = $c->get('settings')['logger'];
    $logger = new Monolog\Logger($settings['name']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], $settings['level']));
    return $logger;
};

$container['db'] = function ($c) {
    $settings = $c->get('settings')['db'];
    $capsule = new Illuminate\Database\Capsule\Manager();
    $capsule->addConnection([
        'driver'    => 'mysql',
        'host'      => $settings['host'],
        'database'  => $settings['name'],
        'username'  => $settings['user'],
        'password'  => $settings['pass'],
        'charset'   => 'utf8',
        //           'timezone'   => '+03:00',
     //   'timezone'   => date('P'),
        'collation' => 'utf8_unicode_ci',
    ]);
    $capsule->setFetchMode(PDO::FETCH_ASSOC);
    $capsule->setAsGlobal();
    if ($pdo = $capsule::connection()->getPdo()) {
        $pdo->query('SET sql_mode = ""');
    }
    return $capsule->getConnection();
};

$container['redis'] = function ($c) {
    $settings = $c->get('settings')['redis'];
    if (extension_loaded('redis') === false) {
        throw new Exception('Redis extension not loaded!');
    }
    $redis = new Redis();
    $connected = $redis->connect($settings['host'], $settings['port']);
    if ($connected === false) {
        throw new Exception('Redis connection fail!');
    }
    $authed = true;
    if ($settings['pass']) {
        $authed = $redis->auth($settings['pass']);
    }
    if ($authed === false) {
        throw new Exception('Redis connection fail!');
    }
    return $redis;
};

$container['stats_manager'] = function ($c) {
    return new app\StatsManager($c['db']);
};

$container['stats_calc'] = function ($c) {
    return new app\StatsCalc($c['db']);
};

$container['stats_import'] = function ($c) {
    return new app\StatsImport($c['stats_manager'], $c['redis']);
};
$container['stats_aggregate'] = function ($c) {
    return new app\StatsAggregate($c['stats_manager'], $c['db']);
};

$container['datetime_handler'] = function ($c) {
    return new app\DateTimeHandler();
};