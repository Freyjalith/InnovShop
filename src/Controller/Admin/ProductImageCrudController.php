<?php

namespace App\Controller\Admin;

use App\Entity\ProductImage;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use Doctrine\ORM\EntityManagerInterface;

class ProductImageCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ProductImage::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            AssociationField::new('product', 'Produit'),
            ImageField::new('filename', 'Image')
                ->setBasePath('/uploads/product/')
                ->setUploadDir('public/uploads/product/')
                ->setUploadedFileNamePattern('[randomhash].[extension]'),
            TextField::new('altText', 'Texte alternatif'),
            BooleanField::new('isPrimary', 'Image principale'),
        ];
    }
    
    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance->isPrimary()) {
            $this->unsetOtherPrimaryImages($entityManager, $entityInstance);
        }
        
        parent::persistEntity($entityManager, $entityInstance);
    }
    
    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance->isPrimary()) {
            $this->unsetOtherPrimaryImages($entityManager, $entityInstance);
        }
        
        parent::updateEntity($entityManager, $entityInstance);
    }
    
    private function unsetOtherPrimaryImages(EntityManagerInterface $entityManager, ProductImage $currentImage): void
    {
        $otherImages = $entityManager->getRepository(ProductImage::class)
            ->createQueryBuilder('pi')
            ->where('pi.product = :product')
            ->andWhere('pi.isPrimary = true')
            ->andWhere('pi.id != :currentId')
            ->setParameter('product', $currentImage->getProduct())
            ->setParameter('currentId', $currentImage->getId() ?? 0)
            ->getQuery()
            ->getResult();
            
        foreach ($otherImages as $image) {
            $image->setIsPrimary(false);
            $entityManager->persist($image);
        }
    }
}