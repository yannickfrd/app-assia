<?php

namespace App\Form\Accommodation;

use App\Entity\Accommodation;
use App\Entity\Device;
use App\Entity\Service;
use App\Entity\SubService;
use App\Form\Type\LocationType;
use App\Form\Utils\Choices;
use App\Repository\DeviceRepository;
use App\Repository\ServiceRepository;
use App\Repository\SubServiceRepository;
use App\Security\CurrentUserService;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AccommodationType extends AbstractType
{
    protected $currentUser;

    public function __construct(CurrentUserService $currentUser)
    {
        $this->currentUser = $currentUser;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('name', null, [
            'attr' => [
                'placeholder' => 'Nom du groupe de places',
            ],
            'help' => 'Ce nom doit vous permettre de retrouver facilement ce logement ou cet hébergement (numéro, couleur...).',
        ])
        ->add('service', EntityType::class, [
            'class' => Service::class,
            'choice_label' => 'name',
            'query_builder' => function (ServiceRepository $repo) {
                return $repo->getServicesFromUserQueryList($this->currentUser);
            },
            'placeholder' => 'placeholder.select',
            ])
        ->add('nbPlaces')
        ->add('startDate', DateType::class, [
            'widget' => 'single_text',
            'label' => 'Opening date',
            'required' => false,
        ])
        ->add('endDate', DateType::class, [
            'widget' => 'single_text',
            'label' => 'Closing date',
            'required' => false,
        ])
        ->add('accommodationType', ChoiceType::class, [
            'choices' => Choices::getChoices(Accommodation::ACCOMMODATION_TYPE),
            'placeholder' => 'placeholder.select',
            'help' => 'Chambre, T1, T2, T3...',
            'required' => false,
        ])
        ->add('configuration', ChoiceType::class, [
            'choices' => Choices::getChoices(Accommodation::CONFIGURATION),
            'placeholder' => 'placeholder.select',
            'help' => 'Diffus ou regroupé',
            'required' => false,
        ])
        ->add('individualCollective', ChoiceType::class, [
            'choices' => Choices::getChoices(Accommodation::INDIVIDUAL_COLLECTIVE),
            'placeholder' => 'placeholder.select',
            'required' => false,
        ])
        ->add('rentAmt', MoneyType::class, [
            'attr' => [
                'class' => 'js-money text-right',
            ],
            'required' => false,
        ])
        ->add('location', LocationType::class, [
            'data_class' => Accommodation::class,
                'attr' => [
                    'geoLocation' => true,
                    'seachLabel' => 'Adresse du groupe de places',
                ],
        ])
        ->add('comment', null, [
            'attr' => [
                'rows' => 5,
                'placeholder' => 'Description...',
            ],
        ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $accommodation = $event->getData();

            $event->getForm()
                ->add('subService', EntityType::class, [
                    'class' => SubService::class,
                    'choice_label' => 'name',
                    'query_builder' => function (SubServiceRepository $repo) {
                        return $repo->getSubServicesFromUserQueryList($this->currentUser);
                    },
                    'placeholder' => 'placeholder.select',
                ])
                ->add('device', EntityType::class, [
                'class' => Device::class,
                'choice_label' => 'name',
                'query_builder' => function (DeviceRepository $repo) use ($accommodation) {
                    return $repo->getDevicesFromServiceQueryList($accommodation->getService());
                },
                'placeholder' => 'placeholder.select',
                ]);
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Accommodation::class,
            'translation_domain' => 'forms',
        ]);
    }
}
