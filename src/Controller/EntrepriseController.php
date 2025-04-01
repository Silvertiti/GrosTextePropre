<?php

namespace App\Controller;

use App\Model\Entreprise;
use Doctrine\ORM\EntityManager;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

class EntrepriseController
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function registerRoutes($app)
    {
        $app->get('/entreprises/create', [self::class, 'createForm']);
        $app->post('/entreprises/create', [self::class, 'store']);
        $app->get('/entreprises/{id}/edit', [self::class, 'editForm']);
        $app->post('/entreprises/{id}/edit', [self::class, 'update']);
        $app->post('/entreprises/{id}/delete', [self::class, 'delete']);
        $app->post('/entreprises/new', [EntrepriseController::class, 'store']);
    }

    public function createForm(Request $request, Response $response): Response
    {
        $view = Twig::fromRequest($request);
        return $view->render($response, 'create_entreprise.twig');
    }
    
        public function store(Request $request, Response $response): Response
        {
            $data = $request->getParsedBody();
            $em = $this->container->get(EntityManager::class);
        
            $entreprise = new Entreprise(
                $data['siret'],
                $data['email'],
                $data['telephone'],
                $data['nom'],
                $data['note'],
                $data['site'],
                $data['description']
            );
        
            $em->persist($entreprise);
            $em->flush();
        
            return $response->withHeader('Location', '/parametres')->withStatus(302);
        }

    public function editForm(Request $request, Response $response, array $args): Response
    {
        $em = $this->container->get(EntityManager::class);
        $entreprise = $em->getRepository(Entreprise::class)->find($args['id']);

        if (!$entreprise) {
            $response->getBody()->write("Entreprise non trouvée.");
            return $response->withStatus(404);
        }

        $view = Twig::fromRequest($request);
        return $view->render($response, 'edit_entreprise.twig', [
            'entreprise' => $entreprise
        ]);
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        $em = $this->container->get(EntityManager::class);
        $entreprise = $em->getRepository(Entreprise::class)->find($args['id']);
    
        if (!$entreprise) {
            $response->getBody()->write("Entreprise introuvable.");
            return $response->withStatus(404);
        }
    
        $data = $request->getParsedBody();
    
        if (isset($data['siret'], $data['nom'], $data['email'], $data['telephone'], $data['note'], $data['site'], $data['description'])) {
            $entreprise->setSIRET($data['siret']);
            $entreprise->setNom($data['nom']);
            $entreprise->setEmail($data['email']);
            $entreprise->setNumeroTelephone($data['telephone']);
            $entreprise->setNoteEvaluation($data['note']);
            $entreprise->setLienSiteWeb($data['site']);
            $entreprise->setDescription($data['description']);
            
            $em->flush(); 
        }
    
        return $response->withHeader('Location', '/parametres')->withStatus(302); 
    }
    

    public function delete(Request $request, Response $response, array $args): Response
    {
        $em = $this->container->get(EntityManager::class);
        $entreprise = $em->getRepository(Entreprise::class)->find($args['id']);

        if ($entreprise) {
            $em->remove($entreprise);
            $em->flush();
        }

        return $response->withHeader('Location', '/parametres')->withStatus(302);
    }
}
