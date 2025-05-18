<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\PasswordField;
use EasyCorp\Bundle\EasyAdminBundle\Field\PhoneNumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;

class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->onlyOnIndex(),
            EmailField::new('email'), 
            TextField::new('firstname', 'Prénom'),
            TextField::new('name', 'Nom'),
            TextField::new('phone')->setHelp('Numéro de téléphone'),
            DateField::new('birth', 'Date de naissance')->setFormat('dd/MM/yyyy'),
            TextField::new('adress', 'Adresse'),
            TextField::new('city', 'Ville'),
            TextField::new('zipCode', 'Code Postal'),
            TextField::new('country', 'Pays'), 
            ArrayField::new('roles')->setHelp('Rôles de l\'utilisateur'),
            DateField::new('createdAt', 'Date de création')->onlyOnIndex(),
        ];
    }
}