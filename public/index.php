<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use App\Controller\HomeController;

$container = require __DIR__ . '/../bootstrap.php';

AppFactory::setContainer($container);

$app = AppFactory::create();

$twig = Twig::create(__DIR__ . '/../src/View', ['cache' => false]);
$app->add(TwigMiddleware::create($app, $twig));

$container->set(HomeController::class, function () use ($container) {
    return new HomeController($container);
});

$container->get(HomeController::class)->registerRoutes($app);

$app->run();
