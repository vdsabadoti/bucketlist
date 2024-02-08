<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    //installation à la possibilité au projet de pouvoir initialisé du tailwind dedans : taper dans la console =>
    //symfony composer require symfonycasts/tailwind-bundle
    //isntaller la denpendance : telecharger les sources de tailwin :
    //simfony console tailwind:init
    //recompiler les modifs :
    //symfony console tailwind:build --watch

    #[Route('/', name: 'app_main')]
    public function home(): Response
    {
        $year = date_create('2024-02-05');
        $year = date_format($year, 'Y');
        return $this->render('main/index.html.twig', [
            'test_var' => "What a lovely background colour isn't it ?",
        ]);
    }

    #[Route('/about-us', name: 'app_about_us')]
    public function aboutUs(): Response
    {
        return $this->render('main/about-us.html.twig');
    }

}
