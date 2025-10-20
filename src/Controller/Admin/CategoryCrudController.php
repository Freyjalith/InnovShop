<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;

class CategoryCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Category::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('name', 'Nom de la catégorie'),
            AssociationField::new('products', 'Produits')->onlyOnIndex(),
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        $initCategories = Action::new('initCategories', 'Initialiser les catégories')
            ->linkToUrl('/admin/categories/init')
            ->setIcon('fa fa-plus-circle')
            ->setCssClass('btn btn-success');

        return $actions
            ->add(Crud::PAGE_INDEX, $initCategories);
    }
}