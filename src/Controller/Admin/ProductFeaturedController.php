<?php

namespace App\Controller\Admin;

use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProductFeaturedController extends AbstractController
{
    #[Route('/admin/product/{id}/toggle-featured', name: 'admin_product_toggle_featured')]
    public function toggleFeatured(
        int $id,
        ProductRepository $productRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $product = $productRepository->find($id);
        
        if (!$product) {
            throw $this->createNotFoundException('Produit non trouvé');
        }

        $product->setFeatured(!$product->isFeatured());
        $entityManager->flush();

        $status = $product->isFeatured() ? 'ajouté aux produits à la une' : 'retiré des produits à la une';
        $this->addFlash('success', "Le produit \"{$product->getName()}\" a été {$status}.");

        return $this->redirectToRoute('admin', [
            'crudAction' => 'index',
            'crudControllerFqcn' => 'App\\Controller\\Admin\\ProductCrudController'
        ]);
    }
}