<?php

namespace App\Controller;

use App\Entity\Promotion;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class PromotionController extends AbstractController
{
    #[Route('/promotions', methods: ['GET'])]
    public function index(EntityManagerInterface $em): JsonResponse
    {
        $promotions = $em->getRepository(Promotion::class)->findAll();
        return $this->json($promotions);
    }
}