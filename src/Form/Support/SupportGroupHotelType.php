<?php

namespace App\Form\Support;

use App\Form\Type\LocationType;
use Symfony\Component\Form\AbstractType;
use App\Form\HotelSupport\HotelSupportType;
use Symfony\Component\Form\FormBuilderInterface;

class SupportGroupHotelType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->remove('status')
            ->remove('startDate')
            ->remove('endDate')
            ->remove('endStatusComment')
            ->remove('theoreticalEndDate')
            ->add('location', LocationType::class, [
                'data_class' => SupportGroupType::class,
                'attr' => [
                    'commentLocationHelp' => 'hotelSupport.commentLocationHelp',
                ],
            ])
            ->add('hotelSupport', HotelSupportType::class);
    }

    public function getParent()
    {
        return SupportGroupType::class;
    }

    public function getBlockPrefix()
    {
        return 'support';
    }
}
