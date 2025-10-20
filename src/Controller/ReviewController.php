<?php

namespace App\Controller;

use App\Entity\Review;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ReviewController extends AbstractController
{
    #[Route('/avis/produit/{productId}/commande/{orderId}', name: 'app_review_create')]
    #[IsGranted('ROLE_USER')]
    public function create(
        int $productId, 
        int $orderId, 
        Request $request,
        ProductRepository $productRepository,
        OrderRepository $orderRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $user = $this->getUser();
        $product = $productRepository->find($productId);
        $order = $orderRepository->find($orderId);

        if (!$product || !$order || $order->getUser() !== $user || $order->getStatus() !== 'delivered') {
            throw $this->createNotFoundException();
        }

        // Vérifier si l'utilisateur a déjà laissé un avis pour ce produit sur cette commande
        $existingReview = $entityManager->getRepository(Review::class)
            ->findOneBy(['product' => $product, 'user' => $user, 'order' => $order]);

        if ($request->isMethod('POST')) {
            $rating = (int) $request->request->get('rating');
            $comment = $request->request->get('comment');

            if ($rating >= 1 && $rating <= 5 && !empty($comment)) {
                if ($existingReview) {
                    // Modifier l'avis existant
                    $existingReview->setRating($rating);
                    $existingReview->setComment($comment);
                    $message = 'Votre avis a été modifié avec succès !';
                } else {
                    // Créer un nouvel avis
                    $review = new Review();
                    $review->setProduct($product);
                    $review->setUser($user);
                    $review->setOrder($order);
                    $review->setRating($rating);
                    $review->setComment($comment);
                    $entityManager->persist($review);
                    $message = 'Votre avis a été publié avec succès !';
                }

                $entityManager->flush();
                $this->addFlash('success', $message);
                return $this->redirectToRoute('app_orders');
            }

            $this->addFlash('error', 'Veuillez remplir tous les champs correctement.');
        }

        return $this->render('review/create.html.twig', [
            'product' => $product,
            'order' => $order,
            'existingReview' => $existingReview,
        ]);
    }
}