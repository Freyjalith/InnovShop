<?php

namespace App\Service;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Product;
use App\Entity\User;
use App\Repository\CartRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class CartService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private CartRepository $cartRepository,
        private RequestStack $requestStack
    ) {
    }

    public function getCurrentCart(?User $user = null): Cart
    {
        if ($user) {
            $cart = $this->cartRepository->findActiveCartByUser($user);
        } else {
            $sessionId = $this->getSessionId();
            $cart = $this->cartRepository->findActiveCartBySession($sessionId);
        }

        if (!$cart) {
            $cart = new Cart();
            $cart->setSessionId($this->getSessionId());
            if ($user) {
                $cart->setUser($user);
            }
            $this->entityManager->persist($cart);
            $this->entityManager->flush();
        }

        return $cart;
    }

    public function addToCart(Product $product, int $quantity = 1, ?User $user = null): void
    {
        $cart = $this->getCurrentCart($user);
        $existingItem = $cart->getItemByProduct($product);

        if ($existingItem) {
            $existingItem->setQuantity($existingItem->getQuantity() + $quantity);
        } else {
            $cartItem = new CartItem();
            $cartItem->setCart($cart);
            $cartItem->setProduct($product);
            $cartItem->setQuantity($quantity);
            $cartItem->setUnitPrice($product->getCurrentPrice());
            
            $cart->addItem($cartItem);
            $this->entityManager->persist($cartItem);
        }

        $cart->setUpdatedAt(new \DateTimeImmutable());
        $this->entityManager->flush();
    }

    public function updateQuantity(CartItem $cartItem, int $quantity): void
    {
        if ($quantity <= 0) {
            $this->removeFromCart($cartItem);
            return;
        }

        $cartItem->setQuantity($quantity);
        $cartItem->getCart()->setUpdatedAt(new \DateTimeImmutable());
        $this->entityManager->flush();
    }

    public function removeFromCart(CartItem $cartItem): void
    {
        $cart = $cartItem->getCart();
        $cart->removeItem($cartItem);
        $cart->setUpdatedAt(new \DateTimeImmutable());
        
        $this->entityManager->remove($cartItem);
        $this->entityManager->flush();
    }

    public function clearCart(?User $user = null): void
    {
        $cart = $this->getCurrentCart($user);
        
        foreach ($cart->getItems() as $item) {
            $this->entityManager->remove($item);
        }
        
        $cart->setStatus('completed');
        $this->entityManager->flush();
    }

    private function getSessionId(): string
    {
        $session = $this->requestStack->getSession();
        
        // Utiliser l'ID de session Symfony
        if (!$session->isStarted()) {
            $session->start();
        }
        
        $cartId = $session->get('cart_id');
        
        if (!$cartId) {
            // Générer un nouvel ID de panier et le stocker en session
            $cartId = uniqid('cart_', true);
            $session->set('cart_id', $cartId);
        }
        
        return $cartId;
    }
}