<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Service\CartService;
use App\Service\EmailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/checkout')]
class CheckoutController extends AbstractController
{
    public function __construct(
        private CartService $cartService,
        private EntityManagerInterface $entityManager,
        private EmailService $emailService
    ) {
    }

    #[Route('/', name: 'app_checkout')]
    public function index(): Response
    {
        $user = $this->getUser();
        $cart = $this->cartService->getCurrentCart($user);

        if ($cart->getItems()->isEmpty()) {
            $this->addFlash('warning', 'Votre panier est vide');
            return $this->redirectToRoute('app_cart');
        }

        return $this->render('checkout/index.html.twig', [
            'cart' => $cart,
            'user' => $user,
        ]);
    }

    #[Route('/process', name: 'app_checkout_process', methods: ['POST'])]
    public function process(Request $request): Response
    {
        $user = $this->getUser();
        $cart = $this->cartService->getCurrentCart($user);

        if ($cart->getItems()->isEmpty()) {
            $this->addFlash('error', 'Votre panier est vide');
            return $this->redirectToRoute('app_cart');
        }

        // Stocker les informations de checkout en session
        $session = $request->getSession();
        $session->set('checkout_data', [
            'email' => $request->request->get('email'),
            'firstName' => $request->request->get('firstName'),
            'lastName' => $request->request->get('lastName'),
            'phone' => $request->request->get('phone'),
            'address' => $request->request->get('address'),
            'postalCode' => $request->request->get('postalCode'),
            'city' => $request->request->get('city'),
            'country' => $request->request->get('country', 'France')
        ]);

        // Rediriger vers la page de paiement
        return $this->redirectToRoute('app_checkout_payment');
    }

    #[Route('/payment', name: 'app_checkout_payment')]
    public function payment(Request $request): Response
    {
        $user = $this->getUser();
        $cart = $this->cartService->getCurrentCart($user);
        $session = $request->getSession();
        $checkoutData = $session->get('checkout_data');

        if (!$checkoutData || $cart->getItems()->isEmpty()) {
            return $this->redirectToRoute('app_checkout');
        }

        return $this->render('checkout/payment.html.twig', [
            'cart' => $cart,
            'checkoutData' => $checkoutData,
        ]);
    }

    #[Route('/payment/process', name: 'app_checkout_payment_process', methods: ['POST'])]
    public function processPayment(Request $request): Response
    {
        $user = $this->getUser();
        $cart = $this->cartService->getCurrentCart($user);
        $session = $request->getSession();
        $checkoutData = $session->get('checkout_data');
        $paymentMethod = $request->request->get('paymentMethod');

        if (!$checkoutData || $cart->getItems()->isEmpty()) {
            $this->addFlash('error', 'Session expirée, veuillez recommencer');
            return $this->redirectToRoute('app_checkout');
        }

        // Créer la commande
        $order = new Order();
        $order->setUser($user);
        $order->setCustomerEmail($checkoutData['email']);
        $order->setCustomerFirstName($checkoutData['firstName']);
        $order->setCustomerLastName($checkoutData['lastName']);
        $order->setCustomerPhone($checkoutData['phone']);
        
        // Adresse de livraison
        $shippingAddress = sprintf(
            "%s\n%s %s\n%s",
            $checkoutData['address'],
            $checkoutData['postalCode'],
            $checkoutData['city'],
            $checkoutData['country']
        );
        $order->setShippingAddress($shippingAddress);
        $order->setBillingAddress($shippingAddress);
        
        $order->setTotalAmount($cart->getTotalPrice());
        
        // Définir le statut selon la méthode de paiement
        switch ($paymentMethod) {
            case 'bank_transfer':
                $order->setStatus('pending_payment');
                $message = 'Votre commande a été enregistrée. Effectuez le virement pour la valider.';
                break;
            case 'klarna':
                $order->setStatus('pending_payment');
                $message = 'Votre commande Klarna a été enregistrée avec succès !';
                break;
            default:
                $order->setStatus('paid');
                $message = 'Votre paiement a été effectué avec succès !';
        }

        // Ajouter les articles de la commande et décrémenter le stock
        foreach ($cart->getItems() as $cartItem) {
            $orderItem = new OrderItem();
            $orderItem->setOrder($order);
            $orderItem->setProduct($cartItem->getProduct());
            $orderItem->setQuantity($cartItem->getQuantity());
            $orderItem->setUnitPrice($cartItem->getUnitPrice());
            
            // Décrémenter le stock du produit
            $cartItem->getProduct()->decrementStock($cartItem->getQuantity());
            
            $order->addItem($orderItem);
            $this->entityManager->persist($orderItem);
        }

        $this->entityManager->persist($order);
        
        // Vider le panier et la session
        $this->cartService->clearCart($user);
        $session->remove('checkout_data');
        
        $this->entityManager->flush();

        // Envoyer l'email de confirmation
        try {
            $this->emailService->sendOrderConfirmation($order);
        } catch (\Exception $e) {
            // Log l'erreur mais ne pas bloquer la commande
        }

        $this->addFlash('success', $message);
        
        return $this->redirectToRoute('app_order_confirmation', ['orderNumber' => $order->getOrderNumber()]);
    }

    #[Route('/confirmation/{orderNumber}', name: 'app_order_confirmation')]
    public function confirmation(string $orderNumber): Response
    {
        $order = $this->entityManager->getRepository(Order::class)
            ->findOneBy(['orderNumber' => $orderNumber]);

        if (!$order) {
            throw $this->createNotFoundException('Commande non trouvée');
        }

        return $this->render('checkout/confirmation.html.twig', [
            'order' => $order,
        ]);
    }
}