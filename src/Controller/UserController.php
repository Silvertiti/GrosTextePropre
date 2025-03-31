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
        $app->get('/parametres/mon-compte', [UserController::class, 'editForm'])->add(RoleCheckMiddleware::class);
        $app->post('/parametres', [UserController::class, 'update'])->add(RoleCheckMiddleware::class);
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
        $user = new User(
            $data['email'],
            $data['prenom'],
            $data['nom'],
            $data['password'],
            $data['role']
        );

        $em->persist($user);
        $em->flush();
    
        return $response->withHeader('Location', '/parametres')->withStatus(302);
    }

    public function editForm(Request $request, Response $response): Response
    {
        $view = Twig::fromRequest($request);
        $session = $this->container->get('session');
        $userId = $session->get('id');  // L'ID de l'utilisateur connecté
        $em = $this->container->get(EntityManager::class);
        $user = $em->getRepository(User::class)->find($userId);

        if (!$user) {
            $response->getBody()->write("Utilisateur non trouvé.");
            return $response->withStatus(404);
        }

        return $view->render($response, 'edit_user.twig', [
            'title' => 'Modifier mon compte',
            'user' => $user
        ]);
    }
    

    public function update(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $session = $this->container->get('session');
        $userId = $session->get('id');  // ID de l'utilisateur connecté
        $em = $this->container->get(EntityManager::class);
        $user = $em->getRepository(User::class)->find($userId);

        if (!$user) {
            $response->getBody()->write("Utilisateur non trouvé.");
            return $response->withStatus(404);
        }

        // Mise à jour des informations
        $user->setPrenom($data['prenom']);
        $user->setNom($data['nom']);
        $user->setEmail($data['email']);

        // Si un mot de passe est fourni, le mettre à jour
        if (!empty($data['password'])) {
            $user->setPassword(password_hash($data['password'], PASSWORD_BCRYPT));  // Assurez-vous de hasher le mot de passe
        }

        $em->flush();

        return $response->withHeader('Location', '/parametres')->withStatus(302);
    }

}
