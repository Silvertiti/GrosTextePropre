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
use App\Model\StageViews;
use App\Model\Entreprise;
use App\Model\EntrepriseNote;

use App\Middlewares\AdminMiddleware;
use App\Middlewares\UserMiddleware;
use App\Middlewares\TuteurMiddleware;

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
        $app->get('/stages/{id}/postuler', [self::class, 'postuler']);
        $app->post('/stages/{id}/postuler', [self::class, 'postulerStage']);
        $app->post('/favoris/{stageId}/toggle', [self::class, 'toggleFavori']);
        $app->post('/entreprise/{id}/noter', [self::class, 'noterEntreprise']);
        $app->get('/stages/{id}/edit', [HomeController::class, 'editStage'])->add(AdminMiddleware::class);
        $app->post('/stages/{id}/edit', [HomeController::class, 'updateStage'])->add(AdminMiddleware::class);
        $app->post('/stages/{id}/delete', [HomeController::class, 'deleteStage'])->add(AdminMiddleware::class);
    }

    public function editStage(Request $request, Response $response, array $args): Response
    {
        $em = $this->container->get(EntityManager::class);
        $stage = $em->getRepository(Stage::class)->find($args['id']);
    
        if (!$stage) {
            $response->getBody()->write("Stage introuvable.");
            return $response->withStatus(404);
        }
    
        $entreprises = $em->getRepository(Entreprise::class)->findAll(); 
    
        $view = Twig::fromRequest($request);
        return $view->render($response, 'edit_stage.twig', [
            'stage' => $stage,
            'entreprises' => $entreprises
        ]);
    }
    

    public function updateStage(Request $request, Response $response, array $args): Response
    {
        $em = $this->container->get(EntityManager::class);
        $stage = $em->getRepository(Stage::class)->find($args['id']);
    
        if (!$stage) {
            $response->getBody()->write("Stage introuvable.");
            return $response->withStatus(404);
        }
    
        $data = $request->getParsedBody();
    
        $villeNom = trim($data['ville_nom'] ?? '');
        $ville = $em->getRepository(\App\Model\Ville::class)->findOneBy(['nom' => $villeNom]);
    
        if (!$ville) {
            $ville = new \App\Model\Ville();
            $ville->setNom($villeNom);
            $em->persist($ville);
        }
    
        $stage->setTitre($data['titre']);
        $stage->setEntreprise($data['entreprise']);
        $stage->setDescription($data['description']);
        $stage->setDateDebut(new \DateTime($data['dateDebut']));
        $stage->setDateFin(new \DateTime($data['dateFin']));
        $stage->setVille($ville);
        $stage->setMotsCles($data['motsCles'] ?? null);
        $stage->setDisponible(isset($data['disponible']));
    
        $em->flush();
    
        return $response->withHeader('Location', '/parametres')->withStatus(302);
    }
    

    public function deleteStage(Request $request, Response $response, array $args): Response
    {
        $em = $this->container->get(EntityManager::class);
        $stage = $em->getRepository(Stage::class)->find($args['id']);

        if ($stage) {
            $em->remove($stage);
            $em->flush();
        }

        return $response->withHeader('Location', '/parametres')->withStatus(302);
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

        $vuesParStage = [];
        foreach ($offres as $offre) {
            $count = $em->createQueryBuilder()
                ->select('COUNT(v.id)')
                ->from(StageViews::class, 'v')
                ->where('v.stage = :stage')
                ->setParameter('stage', $offre)
                ->getQuery()
                ->getSingleScalarResult();

            $vuesParStage[$offre->getId()] = $count;
        }

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

        $favorisIds = [];
        if ($userId) {
            $favoris = $em->getRepository(Favori::class)->findBy(['user' => $userId]);
            foreach ($favoris as $f) {
                $favorisIds[] = $f->getStage()->getId();
            }
        }

        $entreprises = $em->getRepository(Entreprise::class)->findAll();
        $entreprisesParId = [];
        foreach ($entreprises as $e) {
            $entreprisesParId[$e->getId()] = $e->getNom();
        }
        $entreprisesParId2 = [];
        foreach ($entreprises as $e) {
            $entreprisesParId2[$e->getId()] = $e->getNoteEvaluation();
        }

        $postulesIds = [];
        if ($userId) {
            $candidatures = $em->getRepository(Candidature::class)->findBy(['user' => $userId]);
            foreach ($candidatures as $c) {
                $postulesIds[] = $c->getStage()->getId();
            }
        }

        $flashMessage = $session->get('flash_message');
        $session->delete('flash_message');

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
            'flash_message' => $flashMessage,
            'vuesParStage' => $vuesParStage,
            'entreprisesParId' => $entreprisesParId,
            'entreprisesParId2' => $entreprisesParId2,
            'stagesPostules' => $postulesIds
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
            $response->getBody()->write("Stage non trouvé.");
            return $response->withStatus(404);
        }
    
        $ville = $stage->getVille();  
        $villeNom = ($ville === null || $ville->getId() == 0) ? "Ville non précisée" : $ville->getNom();  
    
        $session = $this->container->get('session');
        $userId = $session->get('idUser');
        $user = $em->getRepository(User::class)->find($userId);
    
        if ($user) {
            $viewRepo = $em->getRepository(StageViews::class);
            $existingView = $viewRepo->findOneBy(['stage' => $stage, 'user' => $user]);
    
            if (!$existingView) {
                $vue = new StageViews($stage, $user);
                $vue->setViewedAt(new \DateTime());
                $em->persist($vue);
                $em->flush();
            }
        }
    
        // 🔁 Ici on retrouve l'objet Entreprise à partir du nom (string)
        $entreprise = $em->getRepository(Entreprise::class)->findOneBy([
            'nom' => $stage->getEntreprise()
        ]);
    
        $entrepriseId = $entreprise ? $entreprise->getId() : null;
    
        $entreprises = $em->getRepository(Entreprise::class)->findAll();
        $entreprisesParId = [];
        foreach ($entreprises as $e) {
            $entreprisesParId[$e->getId()] = $e->getNom();
        }
        $entreprisesParId2 = [];
        foreach ($entreprises as $e) {
            $entreprisesParId2[$e->getId()] = $e->getNoteEvaluation();
        }

        $noteExistante = $em->getRepository(EntrepriseNote::class)->findOneBy([
            'entreprise' => $entreprise,
            'user' => $user
        ]);
    
        $hasAlreadyRated = false;
        if ($user && $entreprise) {
            $noteExistante = $em->getRepository(EntrepriseNote::class)
                ->findOneBy(['entreprise' => $entreprise, 'user' => $user]);
        
            $hasAlreadyRated = $noteExistante !== null;
        }
        $view = Twig::fromRequest($request);
        return $view->render($response, 'postuler_stage.twig', [
            'stage' => $stage,
            'villeNom' => $villeNom,
            'entreprisesParId' => $entreprisesParId,
            'entreprisesParId2' => $entreprisesParId2,
            'hasAlreadyRated' => $hasAlreadyRated
        ]);
    }

    public function noterEntreprise(Request $request, Response $response, array $args): Response
    {
        $em = $this->container->get(EntityManager::class);
        $id = (int) $args['id'];
        $note = (int) ($request->getParsedBody()['note'] ?? 0);
    
        $session = $this->container->get('session');
        $userId = $session->get('idUser');
        $user = $em->getRepository(User::class)->find($userId);
        $entreprise = $em->getRepository(Entreprise::class)->find($id);
    
        if (!$entreprise || !$user || $note < 1 || $note > 5) {
            return $response->withStatus(400)->write("Données invalides.");
        }
    
        $repo = $em->getRepository(EntrepriseNote::class);
        $ancienneNote = $repo->findOneBy(['entreprise' => $entreprise, 'user' => $user]);
    
        if ($ancienneNote) {
            $ancienneNote->setNote($note);
        } else {
            $nouvelleNote = new EntrepriseNote($entreprise, $user, $note);
            $em->persist($nouvelleNote);
        }
    
        $em->flush();
    
        // Calcul de la moyenne
        $notes = $repo->findBy(['entreprise' => $entreprise]);
        $somme = array_sum(array_map(fn($n) => $n->getNote(), $notes));
        $moyenne = count($notes) > 0 ? round($somme / count($notes)) : 0;
    
        $entreprise->setNoteEvaluation($moyenne);
        $em->flush();
    
        return $response->withHeader('Location', '/stages')->withStatus(302);
    }

    
    public function postulerStage(Request $request, Response $response, array $args): Response
    {
        $em = $this->container->get(EntityManager::class);
        $data = $request->getParsedBody();
    
        $stage = $em->getRepository(\App\Model\Stage::class)->find($args['id']);
        if (!$stage) {
            $response->getBody()->write("Stage non trouvé.");
            return $response->withStatus(404);
        }
    
        $session = $this->container->get('session');
        $userId = $session->get('idUser');
        $user = $em->getRepository(\App\Model\User::class)->find($userId);
    
        if (!$user) {
            $response->getBody()->write("Utilisateur non trouvé.");
            return $response->withStatus(404);
        }
    
        // Vérifier si l'utilisateur a déjà postulé
        $existing = $em->getRepository(\App\Model\Candidature::class)->findOneBy([
            'user' => $user,
            'stage' => $stage
        ]);
    
        if ($existing) {
            $session->set('flash_message', 'Vous avez déjà postulé à ce stage.');
            return $response->withHeader('Location', '/stages')->withStatus(302);
        }
    
        // Gérer le fichier CV
        $uploadedFile = $request->getUploadedFiles()['cv'] ?? null;
        if ($uploadedFile && $uploadedFile->getError() === UPLOAD_ERR_OK) {
            $fileName = uniqid('cv_', true) . '.' . pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
            $uploadDirectory = $_SERVER['DOCUMENT_ROOT'] . '/GrosTextePropre/public/uploads/cv/';
    
            if (!is_dir($uploadDirectory)) {
                mkdir($uploadDirectory, 0777, true);
            }
    
            $fullPath = $uploadDirectory . $fileName;
            $uploadedFile->moveTo($fullPath);
    
            $filePath = '/uploads/cv/' . $fileName;
        } else {
            $response->getBody()->write("Aucun fichier CV téléchargé.");
            return $response->withStatus(400);
        }
    
        // Enregistrement de la candidature
        $candidature = new \App\Model\Candidature($stage, $user, $data['motivation']);
        $candidature->setCvPath($filePath);
        $candidature->setCreatedAt(new \DateTime());
    
        $em->persist($candidature);
        $em->flush();
    
        $session->set('flash_message', 'Votre candidature a bien été envoyée.');
    
        return $response->withHeader('Location', '/stages')->withStatus(302);
    }
}
