<?php

namespace App\Controller\Admin;

use App\Entity\Order;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;

class OrderCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Order::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Commande')
            ->setEntityLabelInPlural('Commandes')
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setPageTitle('index', 'Gestion des Commandes')
            ->setPageTitle('detail', fn (Order $order) => sprintf('Commande %s', $order->getOrderNumber()))
            ->setPageTitle('edit', fn (Order $order) => sprintf('Modifier la commande %s', $order->getOrderNumber()));
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::NEW)
            ->disable(Action::DELETE);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('orderNumber', 'N° Commande')
                ->setDisabled(),
            
            TextField::new('customerFirstName', 'Prénom')
                ->setDisabled(),
            
            TextField::new('customerLastName', 'Nom')
                ->setDisabled(),
            
            TextField::new('customerEmail', 'Email')
                ->setDisabled(),
            
            TextField::new('customerPhone', 'Téléphone')
                ->setDisabled(),
            
            NumberField::new('totalAmount', 'Montant Total')
                ->setNumDecimals(2)
                ->setDisabled(),
            
            ChoiceField::new('status', 'Statut')
                ->setChoices([
                    'En attente' => 'pending',
                    'Paiement en attente' => 'pending_payment',
                    'Payé' => 'paid',
                    'En traitement' => 'processing',
                    'Expédié' => 'shipped',
                    'Livré' => 'delivered',
                    'Annulé' => 'cancelled',
                ])
                ->renderAsBadges([
                    'pending' => 'warning',
                    'pending_payment' => 'info',
                    'paid' => 'success',
                    'processing' => 'primary',
                    'shipped' => 'info',
                    'delivered' => 'success',
                    'cancelled' => 'danger',
                ]),
            
            TextareaField::new('shippingAddress', 'Adresse de livraison')
                ->setDisabled()
                ->hideOnIndex(),
            
            AssociationField::new('user', 'Utilisateur')
                ->setDisabled()
                ->hideOnIndex(),
            
            // DateTimeField::new('createdAt', 'Date de création')
            //     ->setDisabled(),
        ];
    }
}