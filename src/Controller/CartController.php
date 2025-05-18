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

class CartController extends AbstractController
{

    private $entityManager;

    // Injection de l'EntityManagerInterface dans le constructeur
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/cart/add/{slug}', name: 'add_to_cart', methods: ['POST'])]
    public function addToCart(string $slug, ProductRepository $productRepository, Request $request): Response
    {
        $product = $productRepository->findOneBy(['slug' => $slug]);

        if (!$product) {
            throw $this->createNotFoundException('Produit non trouvé');
        }

        $size = $request->request->get('size');
        if (!in_array($size, ['XS', 'S', 'M', 'L', 'XL'])) {
            $this->addFlash('danger', 'Taille invalide.');
            return $this->redirectToRoute('product_show', ['slug' => $slug]);
        }

        $session = $request->getSession();
        $cart = $session->get('cart', []);

        $key = $product->getId() . '_' . $size;

        if (isset($cart[$key])) {
            $cart[$key]['quantity']++;
        } else {
            $cart[$key] = [
                'product_id' => $product->getId(),
                'size' => $size,
                'quantity' => 1,
            ];
        }

        $session->set('cart', $cart);

        $this->addFlash('success', 'Produit ajouté au panier.');

        return $this->redirectToRoute('product_show', ['slug' => $slug]);
    }

    #[Route('/cart/remove/{key}', name: 'remove_from_cart')]
    public function removeFromCart(string $key, Request $request): Response
    {
        $session = $request->getSession();
        $cart = $session->get('cart', []);

        if (isset($cart[$key])) {
            unset($cart[$key]);
            $session->set('cart', $cart);
            $this->addFlash('success', 'Produit retiré du panier.');
        }

        return $this->redirectToRoute('cart_index');
    }

    #[Route('/cart/increase/{key}', name: 'increase_quantity')]
    public function increaseQuantity(string $key, Request $request): Response
    {
        $session = $request->getSession();
        $cart = $session->get('cart', []);

        if (isset($cart[$key])) {
            $cart[$key]['quantity']++;
            $session->set('cart', $cart);
            $this->addFlash('success', 'Quantité augmentée.');
        }

        return $this->redirectToRoute('cart_index');
    }

    #[Route('/cart/decrease/{key}', name: 'decrease_quantity')]
    public function decreaseQuantity(string $key, Request $request): Response
    {
        $session = $request->getSession();
        $cart = $session->get('cart', []);

        if (isset($cart[$key])) {
            if ($cart[$key]['quantity'] > 1) {
                $cart[$key]['quantity']--;
            } else {
                unset($cart[$key]);
            }
            $session->set('cart', $cart);
            $this->addFlash('success', 'Quantité mise à jour.');
        }

        return $this->redirectToRoute('cart_index');
    }

    #[Route('/cart', name: 'cart_index')]
    public function index(Request $request): Response
    {
        $session = $request->getSession();
        $cart = $session->get('cart', []);

        $cartItems = [];

        foreach ($cart as $key => $entry) {
            // Vérifie si l'entrée est bien un tableau (nouveau format)
            if (!is_array($entry) || !isset($entry['product_id'], $entry['size'], $entry['quantity'])) {
                continue; // ignore les anciennes entrées ou celles mal formées
            }

            $product = $this->entityManager->getRepository(Product::class)->find($entry['product_id']);

            if ($product) {
                $cartItems[] = [
                    'product' => $product,
                    'size' => $entry['size'],
                    'quantity' => $entry['quantity'],
                    'key' => $key
                ];
            }
        }

        // Calcul du sous-total
        $subTotal = $this->total($cartItems);

        // Calcul des frais de port (5% du sous-total)
        $shippingCost = $subTotal * 0.05;

        // Calcul du total
        $total = $subTotal + $shippingCost;

        return $this->render('cart/index.html.twig', [
            'cartItems' => $cartItems,
            'subTotal' => $subTotal,
            'shippingCost' => $shippingCost,
            'total' => $total,
        ]);
    }

    // Calcul du total du panier
    private function total(array $cartItems): float
    {
        $total = 0;
        foreach ($cartItems as $item) {
            $total += $item['product']->getPrice() * $item['quantity'];
        }
        return $total;
    }
}
