<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Repository\ProductRepository;
use App\Repository\CartRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class OrderController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/valider', name: 'app_order_checkout')]
    #[IsGranted('ROLE_USER')]
    public function checkout(
        Request $request,
        ProductRepository $productRepository,
        SessionInterface $session,
        UserInterface $user
    ): Response {
        $cart = $session->get('cart', []);
        
        if (empty($cart)) {
            $this->addFlash('danger', 'Votre panier est vide.');
            return $this->redirectToRoute('cart_index');
        }

        // Créer la commande
        $order = new Order();
        $order->setUser($user);
        $order->setCreatedAt(new \DateTimeImmutable());
        $order->setStatus('Confirmé');  // Par exemple, 'En attente'

        // Calculer le sous-total et ajouter les items
        $subTotal = 0;
        foreach ($cart as $key => $entry) {
            $product = $productRepository->find($entry['product_id']);
            if ($product) {
                $orderItem = new OrderItem();
                $orderItem->setProduct($product);
                $orderItem->setQuantity($entry['quantity']);
                $orderItem->setPrice($product->getPrice());
                $orderItem->setTaille($entry['size']);
                $orderItem->setOrderRelation($order);

                $this->entityManager->persist($orderItem);

                $subTotal += $product->getPrice() * $entry['quantity'];
            }
        }

        // Calculer les frais de port (5% du sous-total)
        $shippingPrice = $subTotal * 0.05;

        // Calculer le total (sous-total + frais de port)
        $total = $subTotal + $shippingPrice;

        // Finaliser la commande
        $order->setTotal($total);
        $order->setShippingPrice($shippingPrice);

        $this->entityManager->persist($order);
        $this->entityManager->flush();

        // Vider le panier
        $session->remove('cart');

        $this->addFlash('success', 'Votre commande a bien été passée.');

        // Rediriger vers une page de confirmation ou d'historique
        return $this->redirectToRoute('order_success', ['orderId' => $order->getId()]);
    }

    #[Route('/commande/{orderId}', name: 'order_success')]
    public function orderSuccess(int $orderId): Response
    {
        // Charger la commande
        $order = $this->entityManager->getRepository(Order::class)->find($orderId);

        if (!$order) {
            throw $this->createNotFoundException('Commande non trouvée');
        }

        return $this->render('order/success.html.twig', [
            'order' => $order,
        ]);
    }

    // #[Route('/commandes', name: 'order_history')]
    // #[IsGranted('ROLE_USER')]
    // public function orderHistory(UserInterface $user): Response
    // {
    //     // Récupérer les commandes de l'utilisateur
    //     $orders = $this->entityManager->getRepository(Order::class)->findBy(['user' => $user]);

    //     return $this->render('order/history.html.twig', [
    //         'orders' => $orders,
    //     ]);
    // }
}