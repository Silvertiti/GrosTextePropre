<?php
require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use App\Controller\HomeController;
use Psr\Container\ContainerInterface; // <-- Add this missing import
use DI\Container;

AppFactory::setContainer(require_once __DIR__ . '/../bootstrap.php');

$app = AppFactory::create();

$twig = Twig::create(__DIR__ . '/../src/View', ['cache' => false]);
$app->add(TwigMiddleware::create($app, $twig));

$container->set(\App\HomeController::class, function (ContainerInterface $container) {
    return new HomeController($container);
});
$container->get(\App\HomeController::class)->registerRoutes($app);

$app->run();
