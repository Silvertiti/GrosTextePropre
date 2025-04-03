<?php

namespace App\Controller;

use Slim\Views\Twig;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Container\ContainerInterface;
use Doctrine\ORM\EntityManager;
use App\Model\User;
use App\Model\Stage;
use App\Model\Entreprise;
use App\Model\StageViews;
use App\Model\Candidature;
use App\Middlewares\UserMiddleware;

class ParametreController
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function registerRoutes($app)
    {
        $app->get('/parametres', [self::class, 'parametres'])->add(UserMiddleware::class);
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

        $entreprisesParId = [];
        foreach ($entreprises as $e) {
            $entreprisesParId[$e->getId()] = $e->getNom();
        }

        $vuesParStage = [];
        foreach ($offres as $offre) {
            $vuesParStage[$offre->getId()] = $em->createQueryBuilder()
                ->select('COUNT(v.id)')
                ->from(StageViews::class, 'v')
                ->where('v.stage = :stage')
                ->setParameter('stage', $offre)
                ->getQuery()
                ->getSingleScalarResult();
        }

        $candidaturesParStage = [];
        foreach ($offres as $offre) {
            $candidaturesParStage[$offre->getId()] = count(
                $em->getRepository(Candidature::class)->findBy(['stage' => $offre])
            );
        }

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

        $candidaturesParEtudiant = [];
        foreach ($students as $student) {
            $candidaturesParEtudiant[$student->getId()] = count(
                $em->getRepository(Candidature::class)->findBy(['user' => $student])
            );
        }

        $vuesParEntreprise = [];
        foreach ($entreprises as $entreprise) {
            $total = 0;
            foreach ($offres as $offre) {
                if ((string) $offre->getEntreprise() === (string) $entreprise->getId()) {
                    $total += $vuesParStage[$offre->getId()] ?? 0;
                }
            }
            $vuesParEntreprise[$entreprise->getId()] = $total;
        }

        $candidatures = $em->getRepository(Candidature::class)->findAll();

        $candidaturesPrenomsParEntreprise = [];
        foreach ($entreprises as $entreprise) {
            $prenoms = [];
            foreach ($offres as $offre) {
                if ((string) $offre->getEntreprise() === (string) $entreprise->getNom()) {
                    $cands = $em->getRepository(Candidature::class)->findBy(['stage' => $offre]);
                    foreach ($cands as $c) {
                        $prenoms[] = $c->getUser()->getPrenom();
                    }
                }
            }
            $candidaturesPrenomsParEntreprise[$entreprise->getId()] = $prenoms;
        }

        return $view->render($response, 'parametres.twig', [
            'title' => 'Settings',
            'session' => [
                'role' => $session->get('role'),
                'idUser' => $session->get('idUser')
            ],
            'students' => $students,
            'tuteurs' => $tuteurs,
            'offres' => $offres,
            'entreprises' => $entreprises,
            'entreprisesParId' => $entreprisesParId,
            'vuesParStage' => $vuesParStage,
            'vuesParEntreprise' => $vuesParEntreprise,
            'candidaturesParStage' => $candidaturesParStage,
            'candidatures' => $candidatures,
            'candidaturesPrenomsParEntreprise' => $candidaturesPrenomsParEntreprise,
        ]);
    }
}
