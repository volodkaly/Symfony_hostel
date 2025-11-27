<?php

namespace App\Controller\Admin;

use App\Entity\Booking;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;

class BookingCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Booking::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            DateField::new('start_date'),
            DateField::new('end_date'),
            BooleanField::new('is_paid'),
            AssociationField::new('customer'),
            AssociationField::new('room'),
        ];
    }

}
