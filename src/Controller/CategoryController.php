<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CategoryController extends AbstractController
{
    #[Route('/category', name: 'app_category')]
    public function index(CategoryRepository $categoryRepository): Response
    {
        // // Récupére les catégories avec un parent_id (enfants)
        // $categoriesWithParent = $categoryRepository->findBy(['parent' => null]);

        // // Récupére les catégories parentes, ici "clubs" et "équipes nationales"
        // $parentCategories = $categoryRepository->findBy(['parent' => null]);

        // return $this->render('category/index.html.twig', [
        //     'parentCategories' => $parentCategories,
        //     'categoriesWithParent' => $categoriesWithParent
        // ]);

        return $this->redirectToRoute('app_home');
    }
}
