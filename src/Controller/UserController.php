<?php

namespace App\Controller;

use App\Model\User;
use Doctrine\ORM\EntityManager;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

class UserController
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function registerRoutes($app)
    {
        $app->get('/register', [self::class, 'createForm']);
        $app->post('/register', [self::class, 'store']);
    }

    public function createForm(Request $request, Response $response): Response
    {
        $view = Twig::fromRequest($request);
        return $view->render($response, 'create_user.twig', [
            'title' => 'Créer un utilisateur'
        ]);
    }

    public function store(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $em = $this->container->get(EntityManager::class);

        $user = new User($data['email'], $data['name']);
        $user->setMotDePasse(password_hash($data['password'], PASSWORD_BCRYPT));
        $user->setRole($data['role']);

        $em->persist($user);
        $em->flush();

        return $response->withHeader('Location', '/parametres')->withStatus(302);
    }
}
