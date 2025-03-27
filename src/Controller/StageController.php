<?php

namespace App\Controller;

use Slim\Views\Twig;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Container\ContainerInterface;
use Doctrine\ORM\EntityManager;
use App\Model\Stage;

class StageController
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function registerRoutes($app)
    {
        $app->get('/stages', \App\Controller\StageController::class . ':stages');
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
                ->where('s.disponible = true');
        
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
                ->where('s.disponible = true');
        
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
    }