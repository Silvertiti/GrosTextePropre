<?php

namespace App\Controller;

use Slim\Views\Twig;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Container\ContainerInterface;
use Doctrine\ORM\EntityManager;
use App\Model\User;
use App\Model\Stage;
use App\Model\Ville;
use App\Model\Entreprise;
use DateTime;
use App\Model\StageViews;
use App\Model\Candidature;


use App\Middlewares\AdminMiddleware;
use App\Middlewares\UserMiddleware;
use App\Middlewares\TuteurMiddleware;

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
        $app->get('/addjob', \App\Controller\HomeController::class . ':addjob')->add(UserMiddleware::class);
        $app->post('/addjob', \App\Controller\HomeController::class . ':storeJob')->add(UserMiddleware::class);
        $app->get('/login', \App\Controller\HomeController::class . ':loginPage')->setName('login');
        $app->post('/login', \App\Controller\HomeController::class . ':processLogin');
        $app->get('/offres', [self::class, 'checkRoleBeforeAccess'])->add(UserMiddleware::class);
        $app->get('/parametres', \App\Controller\HomeController::class . ':parametres')->add(UserMiddleware::class);
        $app->get('/api/villes/search', [self::class, 'searchVilles']);
        $app->get('/mention', \App\Controller\HomeController::class . ':mentionLegales')->add(UserMiddleware::class);
        $app->get('/logout', \App\Controller\HomeController::class . ':logout')->add(UserMiddleware::class);

    }
    public function logout(Request $request, Response $response): Response
    {
        $session = $this->container->get('session');
        $session->delete('idUser');
        $session->delete('role');
        $session->set('flash_message', 'Déconnexion réussie.');
        return $response->withHeader('Location', '/')->withStatus(302);
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
            $stage->setStatus('deleted'); 
    
            $em->persist($stage); 
            $em->flush(); 
        }
    
        return $response->withHeader('Location', '/parametres')->withStatus(302);
    }
    
    public function checkRoleBeforeAccess(Request $request, Response $response): Response
    {
        $role = $this->container->get('session')->get('role');

        if ($role === 'admin') {
            return $response->withHeader('Location', '/stages')->withStatus(302);
        }

        return $response->withHeader('Location', '/')->withStatus(302);
    }

    public function searchVilles(Request $request, Response $response): Response
    {
        $query = $request->getQueryParams()['q'] ?? '';

        $em = $this->container->get(EntityManager::class);
        $results = $em->getRepository(\App\Model\Ville::class)
            ->createQueryBuilder('v')
            ->where('v.nom LIKE :q')
            ->setParameter('q', '%' . $query . '%')
            ->setMaxResults(10)
            ->getQuery()
            ->getArrayResult();

        $payload = json_encode(array_column($results, 'nom'));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }


    public function index(Request $request, Response $response): Response
    {
        $view = Twig::fromRequest($request);
        $session = $this->container->get('session');

        return $view->render($response, 'home.twig', [
            'title' => 'Accueil',
            'message' => 'Bienvenue dans mon projet Slim avec Twig !',
            'session' => [
                'role' => $session->get('role'),
                'idUser' => $session->get('idUser')
            ],
            'test' => $request->getAttribute('user')
        ]);
    }

    public function parametres(Request $request, Response $response): Response
    {
        $view = Twig::fromRequest($request);
        $session = $this->container->get('session');
        $em = $this->container->get(EntityManager::class); 
    
        $role = $session->get('role');
        $userId = $session->get('idUser');
    
        $students = $em->getRepository(User::class)->findBy(['role' => 'user', 'status' => 'active']);
        $tuteurs = $em->getRepository(User::class)->findBy(['role' => 'tuteur', 'status' => 'active']); 
        $offres = $em->getRepository(Stage::class)->findBy(['status' => 'active']);
        $entreprises = $em->getRepository(Entreprise::class)->findAll();
    
        $entreprisesParId = [];
        foreach ($entreprises as $e) {
            $entreprisesParId[$e->getId()] = $e->getNom();
        }
    
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
    
        // Calculer le nombre de candidatures par stage
        $candidaturesParStage = [];
        foreach ($offres as $offre) {
            $candidaturesParStage[$offre->getId()] = count(
                $em->getRepository(Candidature::class)->findBy(['stage' => $offre])
            );
        }
    
        // Calculer le nombre de candidatures par entreprise
        $candidaturesParEntreprise = [];
        foreach ($entreprises as $entreprise) {
            $count = 0;
            foreach ($offres as $offre) {
                if ((string) $offre->getEntreprise() === (string) $entreprise->getId()) {
                    $count += $candidaturesParStage[$offre->getId()] ?? 0;
                }
            }
            $candidaturesParEntreprise[$entreprise->getId()] = $count;
        }
    
        // Calculer les prénoms des utilisateurs par entreprise
        $candidaturesPrenomsParEntreprise = [];
        foreach ($entreprises as $entreprise) {
            $prenoms = [];
    
            foreach ($offres as $offre) {
                if ((string) $offre->getEntreprise() === (string) $entreprise->getId()) {
                    $candidatures = $em->getRepository(Candidature::class)->findBy(['stage' => $offre]);
    
                    foreach ($candidatures as $candidature) {
                        $user = $candidature->getUser();
                        if ($user) {
                            $prenoms[] = $user->getPrenom(); 
                        }
                    }
                }
            }
    
            $candidaturesPrenomsParEntreprise[$entreprise->getId()] = $prenoms;
        }
    
        if ($role == 'user') {
            $candidatures = $em->getRepository(Candidature::class)->findBy(['user' => $userId]);
        } else {
            $candidatures = $em->getRepository(Candidature::class)->findAll();
        }
    
        return $view->render($response, 'parametres.twig', [
            'title' => 'Paramètres',
            'session' => [
                'role' => $role,
                'idUser' => $userId
            ],
            'students' => $students,
            'tuteurs' => $tuteurs, 
            'offres' => $offres,
            'entreprises' => $entreprises,
            'entreprisesParId' => $entreprisesParId,
            'vuesParStage' => $vuesParStage,
            'candidaturesParStage' => $candidaturesParStage,
            'candidatures' => $candidatures,
            'candidaturesPrenomsParEntreprise' => $candidaturesPrenomsParEntreprise, 
        ]);
    }
    
    
    
    

    public function loginPage(Request $request, Response $response): Response
    {
        $view = Twig::fromRequest($request);
        return $view->render($response, 'login.twig');
    }

    public function processLogin(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';

        $em = $this->container->get(EntityManager::class);
        $user = $em->getRepository(User::class)->findOneBy(['email' => $email]);

        if ($user && password_verify($password, $user->getMotDePasse())) {
            $session = $this->container->get('session');
            $session->set('idUser', $user->getId());
            $session->set('role', $user->getRole());

            return $response->withHeader('Location', '/')->withStatus(302);
        }

        $view = Twig::fromRequest($request);
        return $view->render($response, 'login.twig', [
            'error' => 'Email incorrect ou inexistant'
        ]);
    }

    public function login(Request $request, Response $response): Response
    {
        $this->container->get('session')->set('role', 'admin');
        $this->container->get('session')->set('idUser', 1);

        $view = Twig::fromRequest($request);
        return $view->render($response, 'login.twig', [
            'title' => 'login'
        ]);
    }

    public function addjob(Request $request, Response $response): Response
    {
        $em = $this->container->get(EntityManager::class); 
        $villes = $em->getRepository(Ville::class)->findAll();
    
        $view = Twig::fromRequest($request);
        return $view->render($response, 'create_job.twig', [
            'title' => 'Ajouter un stage',
            'villes' => $villes,
            'entreprises' => $em->getRepository(Entreprise::class)->findAll(),
        ]);
    }
    
    
    



    public function storejob(Request $request, Response $response): Response
    {
        $em = $this->container->get(EntityManager::class);
        $data = $request->getParsedBody();
    
        $stage = new Stage();
        $stage->setTitre($data['titre']);
        $stage->setEntreprise($data['entreprise']);
        $stage->setDescription($data['description']);
        $stage->setDateDebut(new DateTime($data['dateDebut']));
        $stage->setDateFin(new DateTime($data['dateFin']));
        $stage->setMotsCles($data['motsCles'] ?? null);
    
        $villeNom = $data['ville_nom'] ?? null;
        $ville = $em->getRepository(Ville::class)->findOneBy(['nom' => $villeNom]);
    
        if (!$ville) {
            $ville = new Ville();
            $ville->setNom($villeNom);
            $em->persist($ville);
        }
    
        $stage->setVille($ville);


        $stage->setCreatedAt(new DateTime('now', new \DateTimeZone('Europe/Paris')));    

        $em->persist($stage);
        $em->flush();
    
        return $response
            ->withHeader('Location', '/stages')
            ->withStatus(302);
    }
    

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

        return $response->withHeader('Location', '/stages')->withStatus(302);
    }

    public function mentionLegales(Request $request, Response $response): Response
    {
        $view = Twig::fromRequest($request);
        return $view->render($response, 'mention_legales.twig');
    }

}