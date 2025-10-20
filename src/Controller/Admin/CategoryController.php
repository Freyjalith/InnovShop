<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/categories')]
#[IsGranted('ROLE_ADMIN')]
class CategoryController extends AbstractController
{
    #[Route('/', name: 'admin_categories')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $categories = $entityManager->getRepository(Category::class)->findAll();
        
        return $this->render('admin/categories/index.html.twig', [
            'categories' => $categories,
        ]);
    }

    #[Route('/create', name: 'admin_category_create')]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($request->isMethod('POST')) {
            $category = new Category();
            $category->setName($request->request->get('name'));
            
            $entityManager->persist($category);
            $entityManager->flush();
            
            $this->addFlash('success', 'Catégorie créée avec succès !');
            return $this->redirectToRoute('admin_categories');
        }
        
        return $this->render('admin/categories/form.html.twig');
    }

    #[Route('/init', name: 'admin_categories_init')]
    public function initCategories(EntityManagerInterface $entityManager): Response
    {
        $categories = ['Audio', 'Wearables', 'VR', 'Drones'];
        
        foreach ($categories as $categoryName) {
            $existing = $entityManager->getRepository(Category::class)->findOneBy(['name' => $categoryName]);
            if (!$existing) {
                $category = new Category();
                $category->setName($categoryName);
                $entityManager->persist($category);
            }
        }
        
        $entityManager->flush();
        $this->addFlash('success', 'Catégories initialisées !');
        
        return $this->redirectToRoute('admin_categories');
    }
}