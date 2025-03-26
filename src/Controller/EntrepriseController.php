<?php

namespace App\Controller;

use App\Entity\Entreprise;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class EntrepriseController extends AbstractController
{
    #[Route('/entreprises', methods: ['GET'])]
    public function index(EntityManagerInterface $em): JsonResponse
    {
        $entreprises = $em->getRepository(Entreprise::class)->findAll();
        return $this->json($entreprises);
    }
}