<?php

namespace App\Controller;

use App\Entity\Wish;
use App\Form\WishType;
use App\Repository\WishRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function PHPUnit\Framework\isNull;

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
    }#[Route('/edit/{id}', name: 'app_edit', requirements: ['id' => '\d+'], defaults: ['id' => 0])]
    public function create(int $id, Request $request, EntityManagerInterface $em, WishRepository $wishRepository): Response
    {
        $wish = new Wish();

        if ($id != 0) {
            $wish = $wishRepository->find($id);
        }

        $form = $this->createForm(WishType::class, $wish);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($wish);
            $em->flush();

            $this->addFlash('success', 'Wish added with succes !');
            return $this->redirectToRoute('app_main');
        }

        return $this->render('wish/edit.html.twig', [
            'form' => $form
        ]);
    }
}
