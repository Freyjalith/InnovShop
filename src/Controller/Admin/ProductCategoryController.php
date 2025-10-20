<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/products-categories')]
#[IsGranted('ROLE_ADMIN')]
class ProductCategoryController extends AbstractController
{
    #[Route('/', name: 'admin_products_categories')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $products = $entityManager->getRepository(Product::class)->findAll();
        $categories = $entityManager->getRepository(Category::class)->findAll();
        
        return $this->render('admin/products_categories.html.twig', [
            'products' => $products,
            'categories' => $categories,
        ]);
    }

    #[Route('/assign/{id}', name: 'admin_product_assign_category', methods: ['POST'])]
    public function assignCategory(Product $product, Request $request, EntityManagerInterface $entityManager): Response
    {
        $categoryId = $request->request->get('category_id');
        
        if ($categoryId) {
            $category = $entityManager->getRepository(Category::class)->find($categoryId);
            $product->setCategory($category);
        } else {
            $product->setCategory(null);
        }
        
        $entityManager->flush();
        $this->addFlash('success', 'Catégorie assignée avec succès !');
        
        return $this->redirectToRoute('admin_products_categories');
    }
}