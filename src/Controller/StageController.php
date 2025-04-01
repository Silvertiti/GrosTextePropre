<?php

namespace App\Controller;

use Slim\Views\Twig;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Container\ContainerInterface;
use Doctrine\ORM\EntityManager;
use App\Model\Stage;
use App\Model\User;
use App\Model\Favori;

class StageController
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function registerRoutes($app)
    {
        $app->get('/stages', [self::class, 'stages']);
        $app->post('/favoris/{stageId}/toggle', [self::class, 'toggleFavori']);
    }

    public function stages(Request $request, Response $response): Response
    {
        $em = $this->container->get(EntityManager::class);
        $session = $this->container->get('session');
        $userId = $session->get('idUser');
        $params = $request->getQueryParams();

        $query = trim($params['q'] ?? '');
        $selectedMotsCles = $params['motscles'] ?? [];
        $favorisOnly = isset($params['favoris']);
        $page = isset($params['page']) ? max(1, (int)$params['page']) : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $qb = $em->createQueryBuilder();
        $qb->select('s')
            ->from(Stage::class, 's')
            ->where('s.disponible = true');

        if (!empty($query)) {
            $qb->andWhere('s.titre LIKE :search OR s.entreprise LIKE :search')
               ->setParameter('search', '%' . $query . '%');
        }

        if (!empty($selectedMotsCles)) {
            $orX = $qb->expr()->orX();
            foreach ($selectedMotsCles as $index => $mot) {
                $orX->add($qb->expr()->like('s.motsCles', ':mot' . $index));
                $qb->setParameter('mot' . $index, '%' . $mot . '%');
            }
            $qb->andWhere($orX);
        }

        if ($favorisOnly && $userId) {
            $favoris = $em->getRepository(Favori::class)->findBy(['user' => $userId]);
            $stageIds = array_map(fn($f) => $f->getStage()->getId(), $favoris);
            if (count($stageIds) > 0) {
                $qb->andWhere($qb->expr()->in('s.id', $stageIds));
            } else {
                $qb->andWhere('1 = 0'); // Aucun favoris
            }
        }

        $qb->setFirstResult($offset)->setMaxResults($limit);
        $offres = $qb->getQuery()->getResult();

        // 🔁 Mots-clés
        $allStages = $em->getRepository(Stage::class)->findAll();
        $motsClesList = [];
        foreach ($allStages as $stage) {
            if ($stage->getMotsCles()) {
                foreach (explode(',', $stage->getMotsCles()) as $mot) {
                    $mot = trim($mot);
                    if ($mot && !in_array($mot, $motsClesList)) {
                        $motsClesList[] = $mot;
                    }
                }
            }
        }
        sort($motsClesList);

        // ❤️ Récupération des favoris actuels
        $favorisIds = [];
        if ($userId) {
            $favoris = $em->getRepository(Favori::class)->findBy(['user' => $userId]);
            foreach ($favoris as $f) {
                $favorisIds[] = $f->getStage()->getId();
            }
        }

        $view = Twig::fromRequest($request);
        return $view->render($response, 'stages.twig', [
            'title' => 'Offres de Stage',
            'offres' => $offres,
            'page' => $page,
            'totalPages' => 1,
            'motsClesList' => $motsClesList,
            'selectedMotsCles' => $selectedMotsCles,
            'query' => $query,
            'favoris' => $favorisIds,
            'now' => new \DateTimeImmutable('now'),
        ]);
    }

    public function toggleFavori(Request $request, Response $response, array $args): Response
    {
        $em = $this->container->get(EntityManager::class);
        $session = $this->container->get('session');
        $userId = $session->get('idUser');
        $stageId = (int)$args['stageId'];

        $user = $em->getRepository(User::class)->find($userId);
        $stage = $em->getRepository(Stage::class)->find($stageId);

        if (!$user || !$stage) {
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }

        $favoriRepo = $em->getRepository(Favori::class);
        $favori = $favoriRepo->findOneBy(['user' => $user, 'stage' => $stage]);

        if ($favori) {
            $em->remove($favori);
            $em->flush();
            $status = 'removed';
        } else {
            $new = new Favori($user, $stage);
            $em->persist($new);
            $em->flush();
            $status = 'added';
        }

        $response->getBody()->write(json_encode(['status' => $status]));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
