<?php

namespace App\Form\Support;

use App\Entity\SubService;
use App\Form\HotelSupport\HotelSupportType;
use App\Form\Type\LocationType;
use App\Repository\SubServiceRepository;
use App\Security\CurrentUserService;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

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
            ->add('subService', EntityType::class, [
                'class' => SubService::class,
                'choice_label' => 'name',
                'query_builder' => function (SubServiceRepository $repo) {
                    return $repo->getSubServicesFromUserQueryList($this->currentUser);
                },
                'placeholder' => 'placeholder.select',
                'help' => 'hotelSupport.subService.help',
            ])
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
