<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Wish;
use App\Form\CategoryType;
use App\Form\WishType;
use App\Repository\CategoryRepository;
use App\Repository\WishRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

class WishController extends AbstractController
{
    #[Route('/wishes', name: 'app_wishes')]
    public function list(Request $request, WishRepository $wishRepository, CategoryRepository $categoryRepository): Response
    {
        $categories = $categoryRepository->findAll();
        $cat = $request->get('category');

        if ($cat == 99){
            return $this->redirectToRoute('app_create_category');
        }

        if ($cat != 0){
            $wishes = $wishRepository->findByCategory($cat);
        } else {
            $wishes = $wishRepository->findAll();
        }

        return $this->render('wish/list.html.twig', [
            'wishes' => $wishes,
            'categories' => $categories
        ]);
    }

    #[Route('/category', name: 'app_create_category')]
    #[IsGranted('ROLE_ADMIN')]
    public function newCategory(Request $request, EntityManagerInterface $em): Response
    {
        $category = new Category();

        $form = $this->createForm(CategoryType::class, $category);

        //GET DATA FROM REQUEST
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($category);
            $em->flush();

            return $this->redirectToRoute('app_wishes');
        }

        return $this->render('wish/category.html.twig', [
            'form' => $form
        ]);

    }

    #[Route('/detail/{id}', name: 'app_detail', requirements: ['id' => '\d+'], defaults: ['id' => 15])]
    public function detail(Wish $wish, WishRepository $wishRepository): Response
    {
        return $this->render('wish/detail.html.twig', [
            'wish' => $wish
        ]);
    }
    #[Route('/delete/{id}', name: 'app_delete', requirements: ['id' => '\d+'])]
    public function delete(Wish $wish, WishRepository $wishRepository, EntityManagerInterface $em): Response
    {

        $em->remove($wish);
        $em->flush();
        return $this->redirectToRoute('app_wishes');

    }
    #[Route('/edit/{id}', name: 'app_edit', requirements: ['id' => '\d+'], defaults: ['id' => 0])]
    public function create(int $id, Request $request, EntityManagerInterface $em, WishRepository $wishRepository, SluggerInterface $slugger): Response
    {
        ///FORM CONSTRUCTION
        //INIT VARIABLES
        $wish = new Wish();
        $update = false;
        $fileName = $fileNameForForm = null;
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
            $fileNameForForm = $form->get('image')->getViewData();
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
            'fileName' => $fileNameForForm
        ]);
    }
}
