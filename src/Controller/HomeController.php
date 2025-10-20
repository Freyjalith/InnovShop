<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use App\Service\EmailService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(ProductRepository $productRepository): Response
    {
        // Les 3 derniers produits ajoutés
        $lastProducts = $productRepository->findBy([], ['id' => 'DESC'], 3);

        // Produits à la une (ceux marqués comme featured)
        $featuredProducts = $productRepository->findBy(['featured' => true], ['id' => 'DESC'], 3);

        return $this->render('home/index.html.twig', [
            'lastProducts' => $lastProducts,
            'featuredProducts' => $featuredProducts,
        ]);
    }

    #[Route('/nouveautes', name: 'nouveautes')]
    public function nouveautes(ProductRepository $productRepository): Response
    {
        $newProducts = $productRepository->findBy(['isNew' => true], ['createdAt' => 'DESC']);

        return $this->render('nouveautes/index.html.twig', [
            'newProducts' => $newProducts,
        ]);
    }

    #[Route('/test-email', name: 'test_email')]
    public function testEmail(MailerInterface $mailer): Response
    {
        try {
            $email = (new Email())
                ->from('elsa.cruzmermy@hotmail.com')
                ->to('elsa.cruzmermy@hotmail.com')
                ->subject('Test Direct - ' . date('H:i:s'))
                ->text('Test direct sans service - ' . date('Y-m-d H:i:s'));

            $mailer->send($email);
            return new Response('✅ Email direct envoyé !');
        } catch (\Exception $e) {
            return new Response('❌ Erreur: ' . $e->getMessage() . '<br>Trace: ' . $e->getTraceAsString());
        }
    }
}