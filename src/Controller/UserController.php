<?php

namespace App\Controller;

use App\Model\Entreprise;
use App\Model\Stage;
use
use App\Model\User;
use Doctrine\ORM\EntityManager;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use App\Middlewares\RoleCheckMiddleware;
use App\Middlewares\AdminMiddleware;

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
        $app->post('/users/{id}/delete', [UserController::class, 'delete'])->add(AdminMiddleware::class);
        $app->get('/users/{id}/edit', [UserController::class, 'editForm'])->add(RoleCheckMiddleware::class);
        $app->post('/users/{id}/edit', [UserController::class, 'update'])->add(RoleCheckMiddleware::class); 
        $app->post('/parametres', [UserController::class, 'updateProfile'])->add(RoleCheckMiddleware::class);
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

    public function delete(Request $request, Response $response, array $args): Response
    {
        $em = $this->container->get(EntityManager::class);
        $user = $em->getRepository(User::class)->find($args['id']);

        if ($user) {
            $em->remove($user);
            $em->flush();
        }

        return $response->withHeader('Location', '/parametres')->withStatus(302);
    }

    public function editForm(Request $request, Response $response, array $args): Response
    {
        $em = $this->container->get(EntityManager::class);
        $view = Twig::fromRequest($request);
        $session = $this->container->get('session');

        $user = $em->getRepository(User::class)->find($args['id']);

        if (!$user) {
            $response->getBody()->write("Utilisateur non trouvé.");
            return $response->withStatus(404);
        }

        $availableRoles = [];
        $role = $session->get('role');

        if ($role === 'admin') {
            $availableRoles = ['user', 'tuteur'];
        } elseif ($role === 'tuteur') {
            $availableRoles = ['user'];
        }

        return $view->render($response, 'edit_user.twig', [
            'user' => $user,
            'availableRoles' => $availableRoles
        ]);
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        $em = $this->container->get(EntityManager::class);
        $data = $request->getParsedBody();
        $user = $em->getRepository(User::class)->find($args['id']);

        if (!$user) {
            $response->getBody()->write("Utilisateur non trouvé.");
            return $response->withStatus(404);
        }

        $user->setPrenom($data['prenom']);
        $user->setNom($data['nom']);
        $user->setEmail($data['email']);
        if (!empty($data['password'])) {
            $user->setMotDePasse(password_hash($data['password'], PASSWORD_BCRYPT));
        }
        $em->flush();

        return $response->withHeader('Location', '/parametres')->withStatus(302);
    }

        public function updateProfile(Request $request, Response $response): Response
    {
        $em = $this->container->get(EntityManager::class);
        $session = $this->container->get('session');
        $userId = $session->get('idUser'); // Récupérer l'ID de l'utilisateur connecté
        $user = $em->getRepository(User::class)->find($userId);

        if (!$user) {
            $response->getBody()->write("Utilisateur non trouvé.");
            return $response->withStatus(404);
        }

        $data = $request->getParsedBody();
        
        // Mise à jour des données de l'utilisateur
        $user->setPrenom($data['prenom']);
        $user->setNom($data['nom']);
        $user->setEmail($data['email']);

        // Mise à jour du mot de passe si fourni
        if (!empty($data['password'])) {
            $user->setMotDePasse(password_hash($data['password'], PASSWORD_BCRYPT));
        }

        $em->flush();

        // Rediriger vers la page des paramètres après la mise à jour
        return $response->withHeader('Location', '/parametres')->withStatus(302);
    }

        
}
