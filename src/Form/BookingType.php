<?php

namespace App\Form;

use App\Entity\Booking;
use App\Entity\Room;
use DateTime;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\MakerBundle\Str;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Constraints\GreaterThan;

class BookingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('start_date', DateType::class, [
                'widget' => 'single_text',
                'attr' => [
                    'value' => (new DateTime())->format('Y-m-d'),
                ]
            ])
            ->add('end_date', DateType::class, [
                'widget' => 'single_text',

            ])
            ->add('room', EntityType::class, [
                'class' => Room::class,
                'choice_label' => 'id',
            ])
            ->add('total_price', HiddenType::class, [
                'mapped' => false,
                'attr' => ['type' => 'hidden'],
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Booking::class,
        ]);
    }
}
