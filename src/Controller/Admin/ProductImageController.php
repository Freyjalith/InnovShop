<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use App\Entity\ProductImage;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin/product/{id}/images', name: 'admin_product_images')]
class ProductImageController extends AbstractController
{
    public function __invoke(
        int $id,
        ProductRepository $productRepository,
        EntityManagerInterface $entityManager,
        Request $request,
        SluggerInterface $slugger
    ): Response {
        $product = $productRepository->find($id);
        
        if (!$product) {
            throw $this->createNotFoundException('Produit non trouvé');
        }

        if ($request->isMethod('POST')) {
            return $this->handleImageUpload($request, $product, $entityManager, $slugger);
        }

        return $this->render('admin/product_images_manage.html.twig', [
            'product' => $product,
        ]);
    }

    private function handleImageUpload(
        Request $request,
        Product $product,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger
    ): Response {
        $action = $request->request->get('action');

        if ($action === 'upload') {
            $uploadedFiles = $request->files->get('images');
            
            if ($uploadedFiles) {
                foreach ($uploadedFiles as $uploadedFile) {
                    if ($uploadedFile) {
                        $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
                        $safeFilename = $slugger->slug($originalFilename);
                        $newFilename = $safeFilename.'-'.uniqid().'.'.$uploadedFile->guessExtension();

                        try {
                            $uploadedFile->move(
                                $this->getParameter('kernel.project_dir').'/public/uploads/product',
                                $newFilename
                            );

                            $productImage = new ProductImage();
                            $productImage->setFilename($newFilename);
                            $productImage->setProduct($product);
                            $productImage->setIsPrimary(count($product->getImages()) === 0);

                            $entityManager->persist($productImage);
                        } catch (FileException $e) {
                            $this->addFlash('error', 'Erreur lors de l\'upload: ' . $e->getMessage());
                        }
                    }
                }
                $entityManager->flush();
                $this->addFlash('success', 'Images ajoutées avec succès');
            }
        } elseif ($action === 'set_primary') {
            $imageId = $request->request->get('image_id');
            $this->setPrimaryImage($product, $imageId, $entityManager);
        } elseif ($action === 'delete') {
            $imageId = $request->request->get('image_id');
            $this->deleteImage($product, $imageId, $entityManager);
        }

        return $this->redirectToRoute('admin_product_images', ['id' => $product->getId()]);
    }

    private function setPrimaryImage(Product $product, int $imageId, EntityManagerInterface $entityManager): void
    {
        foreach ($product->getImages() as $image) {
            $image->setIsPrimary($image->getId() === $imageId);
        }
        $entityManager->flush();
        $this->addFlash('success', 'Image principale définie');
    }

    private function deleteImage(Product $product, int $imageId, EntityManagerInterface $entityManager): void
    {
        foreach ($product->getImages() as $image) {
            if ($image->getId() === $imageId) {
                $filename = $image->getFilename();
                $filePath = $this->getParameter('kernel.project_dir').'/public/uploads/product/'.$filename;
                
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
                
                $entityManager->remove($image);
                break;
            }
        }
        $entityManager->flush();
        $this->addFlash('success', 'Image supprimée');
    }
}