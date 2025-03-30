<?php

namespace App\Controller;

use App\Model\User;
use Doctrine\ORM\EntityManager;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use App\Middlewares\RoleCheckMiddleware;

class UserController
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }


    public function registerRoutes($app)
    {
        $app->get('/register', [UserController::class, 'createForm'])->add(RoleCheckMiddleware::class);
        $app->post('/register', [UserController::class, 'store'])->add(RoleCheckMiddleware::class);
        
    }
    public function createForm(Request $request, Response $response): Response
    {
        $view = Twig::fromRequest($request);
        $session = $this->container->get('session');
        $role = $session->get('role');
    
        $availableRoles = [];
    
        if ($role === 'admin') {
            $availableRoles = ['user', 'tuteur'];
        } elseif ($role === 'tuteur') {
            $availableRoles = ['user'];
        }
    
        return $view->render($response, 'create_user.twig', [
            'title' => 'Créer un utilisateur',
            'availableRoles' => $availableRoles
        ]);
    }
    
    

    public function store(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $em = $this->container->get(EntityManager::class);
        $session = $this->container->get('session');
        $creatorRole = $session->get('role');
    
        $availableRoles = match ($creatorRole) {
            'admin' => ['user', 'tuteur'],
            'tuteur' => ['user'],
            default => [],
        };
    
        if (!in_array($data['role'], $availableRoles)) {
            $response->getBody()->write("Rôle non autorisé.");
            return $response->withStatus(403);
        }
    
        $user = new User($data['email'], $data['name']);
        $user->setMotDePasse(password_hash($data['password'], PASSWORD_BCRYPT));
        $user->setRole($data['role']);
    
        $em->persist($user);
        $em->flush();
    
        return $response->withHeader('Location', '/parametres')->withStatus(302);
    }
    
}
