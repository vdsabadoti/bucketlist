<?php

namespace App\Controller;

use App\Entity\Wish;
use App\Repository\WishRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class WishController extends AbstractController
{
    #[Route('/wishes', name: 'app_wishes')]
    public function list(WishRepository $wishRepository): Response
    {
        $wishes = $wishRepository->findAll();
        return $this->render('wish/list.html.twig', [
            'wishes' => $wishes
        ]);
    }

    #[Route('/detail/{id}', name: 'app_detail', requirements: ['id' => '\d+'], defaults: ['id' => 15])]
    public function detail(Wish $wish, WishRepository $wishRepository): Response
    {
        return $this->render('wish/detail.html.twig', [
            'wish' => $wish
        ]);
    }
}
