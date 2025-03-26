<?php
/*
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
*/
?>
<?php require 'header.php'; ?>

<div class="container mt-4">
    <?php if ($role === 'admin') : ?>
        <h3>Tableau de bord Admin</h3>
        <p>Gestion des utilisateurs et des offres.</p>

    <?php elseif ($role === 'pilote') : ?>
        <h3>Tableau de bord Pilote</h3>
        <p>Gestion des entreprises et des offres.</p>

    <?php elseif ($role === 'etudiant') : ?>
        <h3>Tableau de bord Étudiant</h3>
        <p>Consulter les offres et gérer vos candidatures.</p>

    <?php endif; ?>
</div>

</body>
</html>
