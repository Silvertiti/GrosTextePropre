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
        $app->get('/admin/stages', \App\Controller\HomeController::class . ':adminStages')->add(AdminMiddleware::class);
        $app->post('/admin/stages/{id}/toggle', \App\Controller\HomeController::class . ':toggleDisponibilite');
        $app->get('/login', \App\Controller\HomeController::class . ':loginPage')->setName('login');
        $app->post('/login', \App\Controller\HomeController::class . ':processLogin');
        $app->get('/parametres', \App\Controller\HomeController::class . ':parametres')->add(UserMiddleware::class);
        $app->get('/stages/{id}/edit', [HomeController::class, 'editStage'])->add(AdminMiddleware::class);
        $app->post('/stages/{id}/edit', [HomeController::class, 'updateStage'])->add(AdminMiddleware::class);
        $app->post('/stages/{id}/delete', [HomeController::class, 'deleteStage'])->add(AdminMiddleware::class);
        $app->get('/api/villes/search', [self::class, 'searchVilles']);
        $app->get('/postuler/{id}', \App\Controller\HomeController::class . ':postuler')->add(UserMiddleware::class);
        $app->post('/postuler/{id}/submit', \App\Controller\HomeController::class . ':submitCandidature')->add(UserMiddleware::class);
        $app->get('/mes-candidatures', \App\Controller\HomeController::class . ':mesCandidatures')->add(UserMiddleware::class);
        $app->get('/offres', \App\Controller\HomeController::class . ':offres')->add(UserMiddleware::class);
        $app->post('/stages/{id}/postuler', \App\Controller\HomeController::class . ':submitCandidature')->add(UserMiddleware::class);
    }

    
public function postuler(Request $request, Response $response, array $args): Response
{
    $em = $this->container->get(EntityManager::class);
    $stage = $em->getRepository(Stage::class)->find($args['id']); 

    if (!$stage) {
        $response->getBody()->write("Stage introuvable.");
        return $response->withStatus(404);
    }

    $view = Twig::fromRequest($request);
    return $view->render($response, 'postuler.twig', [
        'offre' => $stage
    ]);
}

    

public function submitCandidature(Request $request, Response $response, array $args): Response
{
    $em = $this->container->get(EntityManager::class);

    // Récupérer les fichiers uploadés
    $uploadedFiles = $request->getUploadedFiles();
    $cvFile = $uploadedFiles['cv'] ?? null;

    // Vérifier si le fichier CV est présent
    if (!$cvFile || $cvFile->getError() !== UPLOAD_ERR_OK) {
        $response->getBody()->write("Erreur : le fichier CV est manquant ou invalide.");
        return $response->withStatus(400);
    }

    // Déplacer le fichier CV
    try {
        $cvFilename = $this->moveUploadedFile('uploads/cv', $cvFile);
    } catch (\Exception $e) {
        $response->getBody()->write("Erreur lors du déplacement du fichier CV : " . $e->getMessage());
        return $response->withStatus(500);
    }

    // Récupérer la motivation
    $parsedBody = $request->getParsedBody();
    $motivation = $parsedBody['motivation'] ?? null;

    if (!$motivation) {
        $response->getBody()->write("Erreur : le champ de motivation est requis.");
        return $response->withStatus(400);
    }

    // Récupérer l'offre et l'utilisateur connecté
    $offreId = $args['id'];
    $etudiantId = $this->container->get('session')->get('idUser');
    $stage = $em->getRepository(Stage::class)->find($offreId);
    $etudiant = $em->getRepository(User::class)->find($etudiantId);

    if (!$stage) {
        $response->getBody()->write("Offre introuvable.");
        return $response->withStatus(404);
    }

    if (!$etudiant) {
        $response->getBody()->write("Utilisateur introuvable.");
        return $response->withStatus(404);
    }

    // Créer une nouvelle candidature
    $candidature = new Candidature();
    $candidature->setEtudiant($etudiant);
    $candidature->setStage($stage);
    $candidature->setCv($cvFilename);
    $candidature->setDateCandidature(new \DateTime());

    $em->persist($candidature);
    $em->flush();

    // Rediriger avec un message de succès
    $response->getBody()->write("Candidature soumise avec succès !");
    return $response->withHeader('Location', '/offres')->withStatus(302);
}


    private function moveUploadedFile(string $directory, $uploadedFile): string
    {
        // Vérifier si le dossier existe, sinon le créer
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true); // Crée le dossier avec des permissions d'écriture
        }
    
        // Vérifier si le dossier est accessible en écriture
        if (!is_writable($directory)) {
            throw new \InvalidArgumentException("Le chemin cible d'upload n'est pas accessible en écriture : $directory");
        }
    
        // Générer un nom de fichier unique
        $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
        $basename = bin2hex(random_bytes(8)); // Générer un nom unique
        $filename = sprintf('%s.%0.8s', $basename, $extension);

        try {
            $uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);
        } catch (\Exception $e) {
            throw new \RuntimeException("Erreur lors du déplacement du fichier : " . $e->getMessage());
        }
    
        return $filename;
    }

    // Autres méthodes non modifiées

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
    
        $students = $em->getRepository(User::class)->findBy(['role' => 'user']);
        $tuteurs = $em->getRepository(User::class)->findBy(['role' => 'tuteur']);
        $offres = $em->getRepository(Stage::class)->findAll();
        $entreprises = $em->getRepository(Entreprise::class)->findAll();            
        return $view->render($response, 'parametres.twig', [
            'title' => 'Settings',
            'session' => [
                'role' => $session->get('role'),
                'idUser' => $session->get('idUser')
            ],
            'students' => $students,
            'tuteurs' => $tuteurs,
            'offres' => $offres,
            'entreprises' => $entreprises        
        ]);
    }

    public function mesCandidatures(Request $request, Response $response): Response
    {
        $em = $this->container->get(EntityManager::class);
        $etudiantId = $this->container->get('session')->get('idUser');

        $candidatures = $em->getRepository(Candidature::class)->findBy(['etudiantId' => $etudiantId]);

        $view = Twig::fromRequest($request);
        return $view->render($response, 'mes_candidatures.twig', [
            'candidatures' => $candidatures
        ]);
    }

    public function offres(Request $request, Response $response): Response
    {
        $em = $this->container->get(EntityManager::class);
        $offres = $em->getRepository(Stage::class)->findAll();
    
        $view = Twig::fromRequest($request);
        return $view->render($response, 'stages.twig', [
            'offres' => $offres
        ]);
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
}