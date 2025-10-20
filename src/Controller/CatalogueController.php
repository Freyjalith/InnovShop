<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CatalogueController extends AbstractController
{
    #[Route('/catalogue', name: 'catalogue')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $search = $request->query->get('search', '');
        $category = $request->query->get('category', '');
        $sort = $request->query->get('sort', 'name_asc');
        $queryBuilder = $entityManager->getRepository(Product::class)
            ->createQueryBuilder('p')
            ->leftJoin('p.category', 'c');

        // Filtre de recherche
        if ($search) {
            $queryBuilder->andWhere('p.name LIKE :search OR p.description LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        // Filtre par catégorie
        if ($category) {
            $queryBuilder->andWhere('c.name = :category')
                ->setParameter('category', $category);
        }

        // Tri
        switch ($sort) {
            case 'price_asc':
                $queryBuilder->orderBy('p.price', 'ASC');
                break;
            case 'price_desc':
                $queryBuilder->orderBy('p.price', 'DESC');
                break;
            case 'name_desc':
                $queryBuilder->orderBy('p.name', 'DESC');
                break;
            default:
                $queryBuilder->orderBy('p.name', 'ASC');
        }

        $products = $queryBuilder->getQuery()->getResult();

        // Récupérer toutes les catégories pour le filtre
        $categories = $entityManager->getRepository(Category::class)->findAll();

        return $this->render('catalogue/index.html.twig', [
            'products' => $products,
            'categories' => $categories,
            'search' => $search,
            'category' => $category,
            'sort' => $sort,
        ]);
    }
}