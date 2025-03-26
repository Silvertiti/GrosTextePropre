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
        $app->get('/login', \App\Controller\HomeController::class . ':login');
        $app->get('/addjob', \App\Controller\HomeController::class . ':addjob');
        $app->post('/addjob', \App\Controller\HomeController::class . ':storeJob');
        $app->get('/admin/stages', \App\Controller\HomeController::class . ':adminStages');
        $app->post('/admin/stages/{id}/toggle', \App\Controller\HomeController::class . ':toggleDisponibilite');
        
        


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

        public function stages(Request $request, Response $response): Response
        {
            $em = $this->container->get(EntityManager::class);
            $params = $request->getQueryParams();
        
            $query = $params['q'] ?? '';
            $page = isset($params['page']) ? max(1, (int)$params['page']) : 1;
            $limit = 10; // nombre d'offres par page
            $offset = ($page - 1) * $limit;
        
            // gestion avec Query appel de la base de donnée en fonction de la recherche
            $qb = $em->createQueryBuilder();
            $qb->select('s')
                ->from(Stage::class, 's')
                ->where('s.disponible = false');
        
            if (!empty($query)) {
                $qb->andWhere('s.titre LIKE :search OR s.entreprise LIKE :search')
                ->setParameter('search', '%' . $query . '%');
            }
        
            $qb->setFirstResult($offset)
            ->setMaxResults($limit);

            // Exécution de la requête → récupération des résultats
            $offres = $qb->getQuery()->getResult();
        
            // Compter le total pour pagination
            $countQb = $em->createQueryBuilder()
                ->select('COUNT(s.id)')
                ->from(Stage::class, 's')
                ->where('s.disponible = false');
        
            if (!empty($query)) {
                $countQb->andWhere('s.titre LIKE :search OR s.entreprise LIKE :search')
                        ->setParameter('search', '%' . $query . '%');
            }
        
            $total = $countQb->getQuery()->getSingleScalarResult();
            $totalPages = ceil($total / $limit);
        
            // affichage avec twig
            $view = Twig::fromRequest($request);
            return $view->render($response, 'stages.twig', [
                'title' => 'Offres de Stage',
                'offres' => $offres,
                'page' => $page,
                'totalPages' => $totalPages,
                'query' => $query
            ]);
        }
    
    
        // Page des login
        public function login(Request $request, Response $response): Response
        {
            $em = $this->container->get(EntityManager::class);
        
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
        

        


}
