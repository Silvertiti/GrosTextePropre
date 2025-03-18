<?php

namespace App\Controller;

use Slim\Views\Twig;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Container\ContainerInterface; // <-- Add this missing import
use Doctrine\ORM\EntityManager;
use App\Model\User;

class HomeController
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
    
   public function registerRoutes($app)
   {
       $app->get('/', \App\HomeController::class . ':index');
   }

    public function index(Request $request, Response $response): Response
    {
        $em = $this->container->get(EntityManager::class);
        $users = $em->getRepository(User::class)->findAll();
        var_dump($users);
        $view = Twig::fromRequest($request);
        return $view->render($response, 'home.twig', [
            'title' => 'Accueil',
            'message' => 'Bienvenue dans mon projet Slim avec Twig !',
            'users' => $users
        ]);
    }
}
