<?php

namespace App\Controller;

use App\Entity\Wish;
use App\Form\WishType;
use App\Repository\WishRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
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
    public function create(int $id, Request $request, EntityManagerInterface $em, WishRepository $wishRepository, SluggerInterface $slugger): Response
    {
        ///FORM CONSTRUCTION
        //INIT VARIABLES
        $wish = new Wish();
        $update = false;
        $fileName = '';
        $deleteImageFromWish = false;

        //VERIFY IF IN CREATION OR UPDATE MODE
        if ($id != 0) {
            $wish = $wishRepository->find($id);
            $update = true;
        }

        //CREATE FORM WITH WISH OBJECT
        $form = $this->createForm(WishType::class, $wish);

        //GET DATA FROM REQUEST
        $form->handleRequest($request);

        //IF UPDATE MODE, GET FILENAME OF IMAGE
        if ($update) {
            $fileName = $form->get('image')->getViewData();
            if ($form->get('shouldDelete')->getNormData()){
                $deleteImageFromWish = true;
            }
        }

        /////FORM VALIDATION ACTIONS
        if ($form->isSubmitted() && $form->isValid()) {

            //CHECK IF IMAGE IS LOADED
           if ($form->get('image_file')->getData() instanceof UploadedFile){
                $file = $form->get('image_file')->getData();
                $fileName = $slugger->slug(mb_strtolower($wish->getTitle())) . '-' . uniqid()  . '.' . $file->guessExtension();
                $file->move($this->getParameter('pictures_dir'), $fileName);

                //CHECK IF IMAGE ALREADY EXISTS (IN CASE OF UPDATE)
               if ($wish->getImage() && \file_exists($this->getParameter('pictures_dir') . '/' . $wish->getImage())) {
                   $deleteImageFromWish = true;
               }
            }

           //THIS FUNCTION IS EXECUTED IF :
            ////IMAGE DELETED IS CHECKED
            /// AN IMAGE WAS UPLOADED FOR A WISH BUT ANOTHER ALREADY EXISTS IN FOLDER
            if ($deleteImageFromWish === true) {
                unlink($this->getParameter('pictures_dir') . '/' . $wish->getImage());
            }

            //UPDATE IMAGE
            $wish->setImage($fileName);

            $em->persist($wish);
            $em->flush();

            $this->addFlash('success', 'Wish added with succes !');
            return $this->redirectToRoute('app_main');
        }

        return $this->render('wish/edit.html.twig', [
            'form' => $form,
            'update' => $update,
            'fileName' => $fileName
        ]);
    }
}
