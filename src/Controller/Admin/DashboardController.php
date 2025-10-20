<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use App\Entity\ProductImage;
use App\Entity\Order;
use App\Entity\User;
use App\Entity\Category;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        // Statistiques
        $stats = [
            'totalOrders' => $this->entityManager->getRepository(Order::class)->count([]),
            'pendingOrders' => $this->entityManager->getRepository(Order::class)->count(['status' => 'pending_payment']),
            'totalProducts' => $this->entityManager->getRepository(Product::class)->count([]),
            'totalUsers' => $this->entityManager->getRepository(User::class)->count([]),
        ];

        return $this->render('admin/dashboard.html.twig', [
            'stats' => $stats
        ]);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('InnovShop Admin');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        yield MenuItem::linkToUrl('Voir le site', 'fa fa-external-link', '/');
        yield MenuItem::section('Gestion');
        yield MenuItem::linkToCrud('Produits', 'fa fa-box', Product::class);
        yield MenuItem::linkToCrud('Catégories', 'fa fa-tags', Category::class);
        yield MenuItem::linkToCrud('Images Produits', 'fa fa-image', ProductImage::class);
        yield MenuItem::linkToUrl('Assigner catégories', 'fa fa-link', $this->generateUrl('admin_products_categories'));
        yield MenuItem::linkToCrud('Commandes', 'fa fa-shopping-cart', Order::class);
        yield MenuItem::linkToCrud('Utilisateurs', 'fa fa-users', User::class);
        yield MenuItem::section('Commandes');
        yield MenuItem::linkToUrl('Toutes les commandes', 'fa fa-list', $this->generateUrl('admin_orders'));
        yield MenuItem::linkToUrl('Paiements en attente', 'fa fa-exclamation-triangle', $this->generateUrl('admin_orders', ['status' => 'pending_payment']));
        yield MenuItem::linkToUrl('Commandes expédiées', 'fa fa-truck', $this->generateUrl('admin_orders', ['status' => 'shipped']));
    }
}