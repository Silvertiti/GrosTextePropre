<?php

namespace App\Controller;

use App\Model\User;
use Doctrine\ORM\EntityManager;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use App\Middlewares\RoleCheckMiddleware;
use App\Middlewares\AdminMiddleware;
use App\Model\Candidature;

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

    public function submitPostuler(Request $request, Response $response, array $args): Response
    {
        $session = $this->container->get('session');
        $userId = $session->get('user_id');
        $stageId = $args['id'];
    
        if (!$userId) {
            return $response->withHeader('Location', '/login')->withStatus(302);
        }
    
        // Récupérer les fichiers téléchargés
        $uploadedFiles = $request->getUploadedFiles();
        $cvFile = $uploadedFiles['cv'] ?? null;
        $ldmFile = $uploadedFiles['ldm'] ?? null;
    
        if ($cvFile && $cvFile->getError() === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../uploads/cvs/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true); // Créer le répertoire si nécessaire
            }
    
            $cvFileName = uniqid() . '-' . $cvFile->getClientFilename();
            $cvFilePath = $uploadDir . $cvFileName;
            $cvFile->moveTo($cvFilePath);
        } else {
            return $response->withStatus(400)->write('Erreur de téléchargement du CV.');
        }
    
        // Optionnel : gérer la lettre de motivation (si téléchargée)
        $ldmFilePath = null;
        if ($ldmFile && $ldmFile->getError() === UPLOAD_ERR_OK) {
            $ldmDir = __DIR__ . '/../../uploads/ldms/';
            if (!file_exists($ldmDir)) {
                mkdir($ldmDir, 0777, true);
            }
    
            $ldmFileName = uniqid() . '-' . $ldmFile->getClientFilename();
            $ldmFilePath = $ldmDir . $ldmFileName;
            $ldmFile->moveTo($ldmFilePath);
        }
    
        // Récupérer l'utilisateur et l'offre de stage
        $em = $this->container->get(EntityManager::class);
        $user = $em->getRepository(User::class)->find($userId);
        $stage = $em->getRepository(Stage::class)->find($stageId);
    
        if (!$user || !$stage) {
            return $response->withHeader('Location', '/stages')->withStatus(302);
        }
    
        // Créer une candidature avec les informations
        $candidature = new Candidature($user, $stage, $cvFilePath, $ldmFilePath);
        $em->persist($candidature);
        $em->flush();
    
        // Rediriger vers la page des stages avec un message de succès
        return $response->withHeader('Location', '/stages?success=1')->withStatus(302);
    }
    
}
