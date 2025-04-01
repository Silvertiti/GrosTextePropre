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
use App\Model\Candidature;

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
        $app->get('/stages/{id}/postuler', \App\Controller\StageController::class . ':postuler');
        $app->post('/stages/{id}/postuler', \App\Controller\StageController::class . ':postulerStage');
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
                $qb->andWhere('1 = 0'); 
            }
        }

        $qb->setFirstResult($offset)->setMaxResults($limit);
        $offres = $qb->getQuery()->getResult();

        // Mots-clés
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

        // Récupération des favoris actuels
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
    public function postuler(Request $request, Response $response, array $args): Response
    {
        $em = $this->container->get(EntityManager::class);
        $stage = $em->getRepository(Stage::class)->find($args['id']);  
        
        if (!$stage) {
            return $response->withStatus(404)->write("Stage non trouvé.");
        }
    
        $ville = $stage->getVille();  
        if ($ville === null || $ville->getId() == 0) {
            $villeNom = "Ville non précisée";  
        } else {
            $villeNom = $ville->getNom();  
        }
    
        $view = Twig::fromRequest($request);
        return $view->render($response, 'postuler_stage.twig', [
            'stage' => $stage,
            'villeNom' => $villeNom  
        ]);
    }
    
    public function postulerStage(Request $request, Response $response, array $args): Response
    {
        $em = $this->container->get(EntityManager::class);
        $data = $request->getParsedBody();
        
        $stage = $em->getRepository(Stage::class)->find($args['id']);
        if (!$stage) {
            return $response->withStatus(404)->getBody()->write("Stage non trouvé.");
        }
    
        $session = $this->container->get('session');
        $userId = $session->get('idUser');
        $user = $em->getRepository(User::class)->find($userId);
    
        if (!$user) {
            return $response->withStatus(404)->getBody()->write("Utilisateur non trouvé.");
        }
    
        $uploadedFile = $request->getUploadedFiles()['cv'] ?? null;
    
        if ($uploadedFile) {
            $fileName = uniqid('cv_', true) . '.' . pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
            
            $uploadDirectory = __DIR__ . '/../../../public/uploads/cvs/';
            
            $uploadedFile->moveTo($uploadDirectory . $fileName);
            
            $filePath = '/uploads/cvs/' . $fileName;
        } else {
            return $response->withStatus(400)->getBody()->write("Aucun fichier CV téléchargé.");
        }
    
        $candidature = new Candidature($stage, $user, $data['motivation']);
        $candidature->setCvPath($filePath); 
    
        $em->persist($candidature);
        $em->flush();
    
        return $response->withHeader('Location', '/stages')->withStatus(302);
    }
    
    
    

    

}
