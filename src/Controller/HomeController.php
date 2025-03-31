<?php

namespace App\Controller;

use Slim\Views\Twig;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Container\ContainerInterface;
use Doctrine\ORM\EntityManager;
use App\Model\User;
use App\Model\Stage;
use App\Middlewares\AdminMiddleware;
use App\Middlewares\UserMiddleware;

class HomeController
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function registerRoutes($app)
    {
        $app->get('/', \App\Controller\HomeController::class . ':index')->add(UserMiddleware::class);
        $app->get('/login', \App\Controller\HomeController::class . ':login')->setName('login');
        $app->get('/addjob', \App\Controller\HomeController::class . ':addjob')->add(UserMiddleware::class);
        $app->post('/addjob', \App\Controller\HomeController::class . ':storeJob')->add(UserMiddleware::class);
        $app->get('/admin/stages', \App\Controller\HomeController::class . ':adminStages')->add(AdminMiddleware::class);
        $app->post('/admin/stages/{id}/toggle', \App\Controller\HomeController::class . ':toggleDisponibilite');
        $app->get('/create', \App\Controller\HomeController::class . ':accountCreate');
        $app->post('/create', \App\Controller\HomeController::class . ':storeAccount');
    }

        // Page d'accueil
        public function index(Request $request, Response $response): Response
        {
            $em = $this->container->get(EntityManager::class);


            $view = Twig::fromRequest($request);
            return $view->render($response, 'home.twig', [
                'title' => 'Accueil',
                'message' => 'Bienvenue dans mon projet Slim avec Twig !',
                'session' => var_export($this->container->get('session')->get('role'),true),
                'test' => $request->getAttribute('user')
            ]);
        }
    
        // Page des login
        public function login(Request $request, Response $response): Response
        {
            $em = $this->container->get(EntityManager::class);
            
            //fake login
            $this->container->get('session')->set('role', 'admin');
            $this->container->get('session')->set('idUser', 1);

            $view = Twig::fromRequest($request);
            return $view->render($response, 'login.twig', [
                'title' => 'login',
            ]);
        }

        // Page des AddJob
        public function addjob(Request $request, Response $response): Response
        {
            $em = $this->container->get(EntityManager::class);
            $offres = $em->getRepository(Stage::class)->findAll();
        
            $view = Twig::fromRequest($request);
            return $view->render($response, 'create_job.twig', [
                'title' => 'AjouterDesJobs',
            ]);
        }

        // Page des AddJob (envoie/ ajout des données ?)
        public function storeJob(Request $request, Response $response): Response
        {
            $data = $request->getParsedBody();

            $stage = new Stage();
            $stage->setTitre($data['titre']);
            $stage->setEntreprise($data['entreprise']);
            $stage->setDescription($data['description']);

            $em = $this->container->get(EntityManager::class);
            $em->persist($stage);
            $em->flush();

            return $response
                ->withHeader('Location', '/stages')
                ->withStatus(302);
        }

        // Page gestion stage
        public function adminStages(Request $request, Response $response): Response
        {
            $em = $this->container->get(EntityManager::class);
            $offres = $em->getRepository(Stage::class)->findAll();
        
            $view = Twig::fromRequest($request);
            return $view->render($response, 'admin_stage.twig', [
                'offres' => $offres
            ]);
        }
        

        public function toggleDisponibilite(Request $request, Response $response, array $args): Response
        {
            $em = $this->container->get(EntityManager::class);
            $stage = $em->getRepository(Stage::class)->find($args['id']);
        
            if ($stage) {
                $stage->setDisponible(!$stage->isDisponible());
                $em->flush();
            }
        
            return $response->withHeader('Location', '/admin/stages')->withStatus(302);
        }
        

        // Page de création de compte etudiant/pilote
        public function accountCreate(Request $request, Response $response): Response {
            return $this->container->get('view')->render($response, 'account_create.twig');
        }
    
        public function storeAccount(Request $request, Response $response): Response
        {
            $data = $request->getParsedBody();
            
            $nom = $data['nom'] ?? '';
            $email = $data['email'] ?? '';
            $password = $data['password'] ?? '';
            $role = $data['role'] ?? '';
    
            if (!$nom || !$email || !$password || !$role) {
                return $response->withJson(['error' => 'Tous les champs sont requis.'], 400);
            }
    
            if (!in_array($role, ['tuteur', 'etudiant'])) {
                return $response->withJson(['error' => 'Rôle invalide.'], 400);
            }
    
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $user = new User($email, $nom, $passwordHash, $role);
            
    
            $em = $this->container->get(EntityManager::class);
            $em->persist($user);
            $em->flush();
    
            return $response->withHeader('Location', '/create')->withStatus(302);
        }
}
