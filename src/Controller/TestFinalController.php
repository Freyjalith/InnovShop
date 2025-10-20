<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class TestFinalController extends AbstractController
{
    #[Route('/test-final', name: 'test_final')]
    public function testFinal(MailerInterface $mailer): Response
    {
        try {
            $email = (new Email())
                ->from('noreply@innovshop.fr')
                ->to('elsa.cruzmermy@hotmail.com')
                ->subject('Test Final InnovShop - ' . date('H:i:s'))
                ->text('Ceci est un test avec votre vraie clé API Brevo !');

            $mailer->send($email);
            
            return new Response('✅ Email envoyé avec votre vraie clé API !');
        } catch (\Exception $e) {
            return new Response('❌ Erreur: ' . $e->getMessage());
        }
    }
}