<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Doctrine\ORM\EntityManagerInterface;

class ProductController extends AbstractController
{
    private $entityManager;

    // Injection de l'EntityManagerInterface dans le constructeur
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/category/{slug}', name: 'product_by_category')]
    public function productsByCategory(string $slug, CategoryRepository $categoryRepository): Response
    {
        // Récupère la catégorie par son slug
        $category = $categoryRepository->findOneBy(['slug' => $slug]);

        if (!$category) {
            throw $this->createNotFoundException('Catégorie introuvable.');
        }

        // dd($category->getProducts());
        // dd($category);

        // Vérifie si la catégorie contient des produits
        if ($category->getProducts()->isEmpty()) {
            // Ajouter un log pour vérifier si la catégorie contient des produits
            $this->addFlash('notice', 'Aucun produit trouvé dans cette catégorie.');
        }

        // Récupère tous les produits associés à cette catégorie
        $products = $category->getProducts();

        // Affiche les produits dans le template
        return $this->render('product/category.html.twig', [
            'category' => $category,
            'products' => $products,
        ]);
    }


    #[Route('/product/{slug}', name: 'product_show')]
    public function show(string $slug): Response
    {
        $product = $this->entityManager
            ->getRepository(Product::class)
            ->findOneBy(['slug' => $slug]);

        if (!$product) {
            throw new NotFoundHttpException('Produit non trouvé');
        }

        // dd($product);

        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }
}
