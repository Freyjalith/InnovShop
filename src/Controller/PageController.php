<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PageController extends AbstractController
{
    #[Route('/promotions', name: 'app_promotions')]
    public function promotions(ProductRepository $productRepository): Response
    {
        $promotionalProducts = $productRepository->findPromotionalProducts();
        
        return $this->render('pages/promotions.html.twig', [
            'products' => $promotionalProducts,
        ]);
    }

    #[Route('/contact', name: 'app_contact')]
    public function contact(): Response
    {
        return $this->render('pages/contact.html.twig');
    }

    #[Route('/faq', name: 'app_faq')]
    public function faq(): Response
    {
        return $this->render('pages/faq.html.twig');
    }

    #[Route('/livraison', name: 'app_shipping')]
    public function shipping(): Response
    {
        return $this->render('pages/shipping.html.twig');
    }

    #[Route('/retours', name: 'app_returns')]
    public function returns(): Response
    {
        return $this->render('pages/returns.html.twig');
    }

    #[Route('/mentions-legales', name: 'app_legal')]
    public function legal(): Response
    {
        return $this->render('pages/legal.html.twig');
    }

    #[Route('/politique-confidentialite', name: 'app_privacy')]
    public function privacy(): Response
    {
        return $this->render('pages/privacy.html.twig');
    }
}