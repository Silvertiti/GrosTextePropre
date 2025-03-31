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

    public function stages(Request $request, Response $response): Response
    {
        $em = $this->container->get(EntityManager::class);
        $params = $request->getQueryParams();

        $query = trim($params['q'] ?? '');
        $selectedMotsCles = $params['motscles'] ?? [];
        $page = isset($params['page']) ? max(1, (int)$params['page']) : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $qb = $em->createQueryBuilder();
        $qb->select('s')
            ->from(Stage::class, 's')
            ->where('s.disponible = true');

        // 🔍 Recherche texte
        if (!empty($query)) {
            $qb->andWhere('s.titre LIKE :search OR s.entreprise LIKE :search')
               ->setParameter('search', '%' . $query . '%');
        }

        // 🔍 Filtres mots-clés
        if (!empty($selectedMotsCles)) {
            $orX = $qb->expr()->orX();
            foreach ($selectedMotsCles as $index => $mot) {
                $orX->add($qb->expr()->like('s.motsCles', ':mot' . $index));
                $qb->setParameter('mot' . $index, '%' . $mot . '%');
            }
            $qb->andWhere($orX);
        }

        $qb->setFirstResult($offset)
            ->setMaxResults($limit);

        $offres = $qb->getQuery()->getResult();

        // 🧮 Pagination
        $countQb = $em->createQueryBuilder();
        $countQb->select('COUNT(s.id)')
                ->from(Stage::class, 's')
                ->where('s.disponible = true');

        if (!empty($query)) {
            $countQb->andWhere('s.titre LIKE :search OR s.entreprise LIKE :search')
                    ->setParameter('search', '%' . $query . '%');
        }

        if (!empty($selectedMotsCles)) {
            $orX = $countQb->expr()->orX();
            foreach ($selectedMotsCles as $index => $mot) {
                $orX->add($countQb->expr()->like('s.motsCles', ':mot' . $index));
                $countQb->setParameter('mot' . $index, '%' . $mot . '%');
            }
            $countQb->andWhere($orX);
        }

        $total = $countQb->getQuery()->getSingleScalarResult();
        $totalPages = ceil($total / $limit);

        // 🏷 Récupérer tous les mots-clés uniques
        $allStages = $em->getRepository(Stage::class)->findAll();
        $motsClesList = [];

        foreach ($allStages as $stage) {
            $mots = $stage->getMotsCles();
            if (!$mots) continue;

            foreach (explode(',', $mots) as $mot) {
                $mot = trim($mot);
                if ($mot && !in_array($mot, $motsClesList)) {
                    $motsClesList[] = $mot;
                }
            }
        }

        sort($motsClesList);

        $view = Twig::fromRequest($request);
        return $view->render($response, 'stages.twig', [
            'title' => 'Offres de Stage',
            'offres' => $offres,
            'page' => $page,
            'totalPages' => $totalPages,
            'motsClesList' => $motsClesList,
            'selectedMotsCles' => $selectedMotsCles,
            'query' => $query
        ]);
    }
}