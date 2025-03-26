<?php

namespace App\Controller;

use Slim\Views\Twig;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Container\ContainerInterface;
use Doctrine\ORM\EntityManager;
use App\Model\User;
use App\Model\Stage;

class HomeController
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function registerRoutes($app)
    {
        $app->get('/', \App\Controller\HomeController::class . ':index');
        $app->get('/stages', \App\Controller\HomeController::class . ':stages');
    }

    // Page d'accueil
    public function index(Request $request, Response $response): Response
    {
        $em = $this->container->get(EntityManager::class);
        $users = $em->getRepository(User::class)->findAll();

        $view = Twig::fromRequest($request);
        return $view->render($response, 'home.twig', [
            'title' => 'Accueil',
            'message' => 'Bienvenue dans mon projet Slim avec Twig !',
            'users' => $users
        ]);
    }

    // Page des stages
    public function stages(Request $request, Response $response): Response
    {
        $em = $this->container->get(EntityManager::class);
        $offres = $em->getRepository(Stage::class)->findAll();
    
        $view = Twig::fromRequest($request);
        return $view->render($response, 'stages.twig', [
            'title' => 'Offres de Stage',
            'offres' => $offres
        ]);
    }
}
