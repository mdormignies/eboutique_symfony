<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use App\Entity\Category;

use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;

class ProductCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Product::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->onlyOnIndex(),
            TextField::new('name', 'Nom du produit'),
            TextEditorField::new('description', 'Description'),
            TextField::new('slug', 'Slug')
                ->setHelp('Utilisé pour générer l\'URL du produit'),

            MoneyField::new('price', 'Prix')
                ->setCurrency('EUR')
                ->setStoredAsCents(false),

            ImageField::new('image', 'Image')
                ->setBasePath('/images')
                ->setUploadDir('public/images'),

            AssociationField::new('categories', 'Catégories')
                ->setFormTypeOptions([
                    'by_reference' => false,  // Pour gérer la relation ManyToMany correctement
                ])
        ];
    }
}
