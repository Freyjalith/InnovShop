<?php

namespace App\Controller\Admin;

use App\Entity\Order;
use App\Service\EmailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/orders')]
#[IsGranted('ROLE_ADMIN')]
class OrderController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private EmailService $emailService
    ) {
    }

    #[Route('/', name: 'admin_orders')]
    public function index(Request $request): Response
    {
        $status = $request->query->get('status');
        $search = $request->query->get('search');
        
        $queryBuilder = $this->entityManager->getRepository(Order::class)
            ->createQueryBuilder('o')
            ->leftJoin('o.user', 'u')
            ->orderBy('o.createdAt', 'DESC');

        if ($status) {
            $queryBuilder->andWhere('o.status = :status')
                ->setParameter('status', $status);
        }

        if ($search) {
            $queryBuilder->andWhere('o.orderNumber LIKE :search OR o.customerEmail LIKE :search OR o.customerFirstName LIKE :search OR o.customerLastName LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        $orders = $queryBuilder->getQuery()->getResult();

        return $this->render('admin/orders/index.html.twig', [
            'orders' => $orders,
            'currentStatus' => $status,
            'search' => $search,
        ]);
    }

    #[Route('/{id}', name: 'admin_order_show')]
    public function show(Order $order): Response
    {
        return $this->render('admin/orders/show.html.twig', [
            'order' => $order,
        ]);
    }

    #[Route('/{id}/status', name: 'admin_order_update_status', methods: ['POST'])]
    public function updateStatus(Order $order, Request $request): Response
    {
        $newStatus = $request->request->get('status');
        $oldStatus = $order->getStatus();
        
        if (in_array($newStatus, ['pending', 'pending_payment', 'paid', 'processing', 'shipped', 'delivered', 'cancelled'])) {
            $order->setStatus($newStatus);
            $this->entityManager->flush();
            
            // Envoyer un email si le statut a changé
            if ($oldStatus !== $newStatus) {
                try {
                    $this->emailService->sendOrderStatusUpdate($order);
                    $this->addFlash('success', 'Statut mis à jour et email envoyé au client.');
                } catch (\Exception $e) {
                    $this->addFlash('warning', 'Statut mis à jour mais erreur lors de l\'envoi de l\'email.');
                }
            } else {
                $this->addFlash('success', 'Statut de la commande mis à jour avec succès.');
            }
        } else {
            $this->addFlash('error', 'Statut invalide.');
        }

        return $this->redirectToRoute('admin_order_show', ['id' => $order->getId()]);
    }
}