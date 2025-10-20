<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\CartItemRepository;
use App\Service\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/cart')]
class CartController extends AbstractController
{
    public function __construct(private CartService $cartService)
    {
    }

    #[Route('/', name: 'app_cart')]
    public function index(): Response
    {
        $user = $this->getUser(); // Peut être null
        $cart = $this->cartService->getCurrentCart($user);

        return $this->render('cart/index.html.twig', [
            'cart' => $cart,
        ]);
    }

    #[Route('/add/{id}', name: 'app_cart_add', methods: ['POST'])]
    public function add(Product $product, Request $request): JsonResponse
    {
        try {
            $quantity = (int) $request->request->get('quantity', 1);
            $user = $this->getUser(); // Peut être null pour les utilisateurs non connectés
            
            $this->cartService->addToCart($product, $quantity, $user);
            $cart = $this->cartService->getCurrentCart($user);

            return new JsonResponse([
                'success' => true,
                'message' => 'Produit ajouté au panier',
                'cartCount' => $cart->getTotalItems()
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Erreur lors de l\'ajout au panier: ' . $e->getMessage()
            ], 500);
        }
    }

    #[Route('/update/{id}', name: 'app_cart_update', methods: ['POST'])]
    public function update(int $id, Request $request, CartItemRepository $cartItemRepository): JsonResponse
    {
        $cartItem = $cartItemRepository->find($id);
        if (!$cartItem) {
            return new JsonResponse(['success' => false, 'message' => 'Article non trouvé'], 404);
        }

        $quantity = (int) $request->request->get('quantity');
        $this->cartService->updateQuantity($cartItem, $quantity);

        $user = $this->getUser();
        $cart = $this->cartService->getCurrentCart($user);

        return new JsonResponse([
            'success' => true,
            'cartTotal' => $cart->getTotalPrice(),
            'cartCount' => $cart->getTotalItems()
        ]);
    }

    #[Route('/remove/{id}', name: 'app_cart_remove', methods: ['POST'])]
    public function remove(int $id, CartItemRepository $cartItemRepository): JsonResponse
    {
        $cartItem = $cartItemRepository->find($id);
        if (!$cartItem) {
            return new JsonResponse(['success' => false, 'message' => 'Article non trouvé'], 404);
        }

        $this->cartService->removeFromCart($cartItem);

        $user = $this->getUser();
        $cart = $this->cartService->getCurrentCart($user);

        return new JsonResponse([
            'success' => true,
            'cartTotal' => $cart->getTotalPrice(),
            'cartCount' => $cart->getTotalItems()
        ]);
    }

    #[Route('/clear', name: 'app_cart_clear', methods: ['POST'])]
    public function clear(): JsonResponse
    {
        $user = $this->getUser();
        $this->cartService->clearCart($user);

        return new JsonResponse([
            'success' => true,
            'message' => 'Panier vidé'
        ]);
    }

    #[Route('/count', name: 'app_cart_count', methods: ['GET'])]
    public function count(): JsonResponse
    {
        $user = $this->getUser();
        $cart = $this->cartService->getCurrentCart($user);

        return new JsonResponse([
            'count' => $cart->getTotalItems()
        ]);
    }
}