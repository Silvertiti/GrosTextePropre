<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use App\Controller\HomeController;
use App\Controller\StageController;
use App\Middlewares\AdminMiddleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use DI\Container;
use Psr\Http\Message\ResponseFactoryInterface;
use Slim\Psr7\Factory\ResponseFactory;
use App\Middlewares\UserMiddleware;
date_default_timezone_set('Europe/Paris');

$container = require __DIR__ . '/../bootstrap.php';

AppFactory::setContainer($container);

$app = AppFactory::create();

$twig = Twig::create(__DIR__ . '/../src/View', ['cache' => false,'debug' => true]);
$twig->addExtension(new \Twig\Extension\DebugExtension());

$app->add(TwigMiddleware::create($app, $twig));

$container->set('view', function () use ($twig) {
    return $twig;
});

$container->set(HomeController::class, function () use ($container) {
    return new HomeController($container);
});

$container->set(StageController::class, function () use ($container) {
    return new StageController($container);
});

$userController = new \App\Controller\UserController($container);
$userController->registerRoutes($app);

$container->set(AdminMiddleware::class, function () use ($container) {
    return new AdminMiddleware($container);
});

$container->set(UserMiddleware::class, function () use ($container) {
    return new UserMiddleware($container);
});

$container->set(ResponseFactoryInterface::class , function () use ($app) {
    return $app->getResponseFactory();
});


$entrepriseController = new \App\Controller\EntrepriseController($container);
$entrepriseController->registerRoutes($app);


$app->add(
    new \Slim\Middleware\Session([
      'name' => 'session',
      'autorefresh' => true,
      'lifetime' => '1 hour',
    ])
);

$container->set('session', function () {
    return new \SlimSession\Helper();
});

$container->get(StageController::class)->registerRoutes($app);
$container->get(HomeController::class)->registerRoutes($app);

$app->run();
