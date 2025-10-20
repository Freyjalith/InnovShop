<?php

namespace App\Service;

use App\Entity\Order;
use App\Entity\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class EmailService
{
    public function __construct(
        private MailerInterface $mailer
    ) {
    }

    public function sendWelcomeEmail(User $user): void
    {
        $email = (new TemplatedEmail())
            ->from(new Address('elsa.cruzmermy@hotmail.fr', 'InnovShop'))
            ->to($user->getEmail())
            ->subject('Bienvenue chez InnovShop !')
            ->htmlTemplate('emails/welcome.html.twig')
            ->context(['user' => $user]);

        $this->mailer->send($email);
    }

    public function sendOrderConfirmation(Order $order): void
    {
        $email = (new TemplatedEmail())
            ->from(new Address('elsa.cruzmermy@hotmail.fr', 'InnovShop'))
            ->to($order->getCustomerEmail())
            ->subject('Confirmation de commande - ' . $order->getOrderNumber())
            ->htmlTemplate('emails/order_confirmation.html.twig')
            ->context(['order' => $order]);

        $this->mailer->send($email);
    }

    public function sendOrderStatusUpdate(Order $order): void
    {
        $statusMessages = [
            'pending' => 'En attente de traitement',
            'pending_payment' => 'En attente de paiement',
            'paid' => 'Paiement confirmé',
            'processing' => 'En cours de préparation',
            'shipped' => 'Expédiée',
            'delivered' => 'Livrée',
            'cancelled' => 'Annulée'
        ];

        $email = (new TemplatedEmail())
            ->from(new Address('elsa.cruzmermy@hotmail.fr', 'InnovShop'))
            ->to($order->getCustomerEmail())
            ->subject('Mise à jour de votre commande - ' . $order->getOrderNumber())
            ->htmlTemplate('emails/order_status_update.html.twig')
            ->context([
                'order' => $order,
                'statusMessage' => $statusMessages[$order->getStatus()] ?? $order->getStatus()
            ]);

        $this->mailer->send($email);
    }

    public function sendTestEmail(): void
    {
        $email = (new TemplatedEmail())
            ->from(new Address('elsa.cruzmermy@hotmail.fr', 'InnovShop'))
            ->to('elsa.cruzmermy@hotmail.fr')
            ->subject('Test Email - ' . date('H:i:s'))
            ->text('Test simple depuis InnovShop - ' . date('Y-m-d H:i:s'));

        $this->mailer->send($email);
    }
}