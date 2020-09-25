<?php

namespace App\Form\Support;

use App\Security\CurrentUserService;
use Symfony\Component\Form\AbstractType;
use App\Form\HotelSupport\HotelSupportType;
use Symfony\Component\Form\FormBuilderInterface;
use App\Form\Accommodation\AccommodationGroupHotelType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class SupportGroupHotelType extends AbstractType
{
    private $currentUser;

    public function __construct(CurrentUserService $currentUser)
    {
        $this->currentUser = $currentUser;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->remove('status')
            ->remove('location')
            ->add('accommodationGroups', CollectionType::class, [
                'entry_type' => AccommodationGroupHotelType::class,
                'label_attr' => [
                    'class' => 'sr-only',
                ],
                'allow_add' => false,
                'allow_delete' => false,
                'delete_empty' => true,
                'label' => null,
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
