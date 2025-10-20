<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use App\Entity\Category;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;

class ProductCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Product::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Produit')
            ->setEntityLabelInPlural('Produits')
            ->setPageTitle('index', 'Gestion des Produits')
            ->setPageTitle('new', 'Ajouter un Produit')
            ->setPageTitle('edit', 'Modifier le Produit')
            ->setPageTitle('detail', 'Détails du Produit');
    }

    public function configureActions(Actions $actions): Actions
    {
        $manageImages = Action::new('manageImages', 'Gérer les images', 'fa fa-images')
            ->linkToRoute('admin_product_images', function (Product $entity) {
                return ['id' => $entity->getId()];
            });

        $toggleFeatured = Action::new('toggleFeatured', 'Basculer à la une', 'fa fa-star')
            ->linkToRoute('admin_product_toggle_featured', function (Product $entity) {
                return ['id' => $entity->getId()];
            })
            ->displayIf(function (Product $entity) {
                return $entity->getId() !== null;
            });

        return $actions
            ->add(Crud::PAGE_INDEX, $manageImages)
            ->add(Crud::PAGE_INDEX, $toggleFeatured)
            ->add(Crud::PAGE_DETAIL, $manageImages)
            ->add(Crud::PAGE_DETAIL, $toggleFeatured);
    }

    public function configureFields(string $pageName): iterable
    {
        $fields = [
            IdField::new('id')->hideOnForm(),
            TextField::new('name', 'Nom du produit')
                ->setColumns(6),
            NumberField::new('price', 'Prix (€)')
                ->setNumDecimals(2)
                ->setColumns(3),
            IntegerField::new('stock', 'Stock')
                ->setColumns(3)
                ->setHelp('Quantité disponible en stock'),
            BooleanField::new('featured', 'À la une')
                ->setHelp('Mettre ce produit en avant sur la page d\'accueil')
                ->renderAsSwitch(false),
            BooleanField::new('isNew', 'Nouveauté')
                ->setHelp('Marquer ce produit comme nouveauté')
                ->renderAsSwitch(false),
            AssociationField::new('category', 'Catégorie')
                ->setColumns(6)
                ->setHelp('Sélectionner la catégorie du produit'),
            NumberField::new('originalPrice', 'Prix original (€)')
                ->setNumDecimals(2)
                ->setColumns(3)
                ->setHelp('Prix avant promotion (optionnel)')
                ->hideOnIndex(),
            IntegerField::new('discountPercentage', 'Réduction (%)')
                ->setColumns(3)
                ->setHelp('Pourcentage de réduction (optionnel)')
                ->hideOnIndex(),
            TextareaField::new('description', 'Description')
                ->setColumns(12)
                ->hideOnIndex(),
            TextareaField::new('specifications', 'Spécifications')
                ->setColumns(12)
                ->setHelp('Spécifications techniques détaillées')
                ->hideOnIndex(),
        ];

        if ($pageName === Crud::PAGE_INDEX || $pageName === Crud::PAGE_DETAIL) {
            $fields[] = AssociationField::new('images', 'Images')
                ->formatValue(function ($value, $entity) {
                    $count = count($entity->getImages());
                    $primary = $entity->getPrimaryImage();
                    $status = $primary ? ' (image principale définie)' : ' (aucune image principale)';
                    return $count . ' image(s)' . $status;
                });
        }

        return $fields;
    }
}