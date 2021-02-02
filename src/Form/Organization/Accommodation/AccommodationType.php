<?php

namespace App\Form\Organization\Accommodation;

use App\Entity\Organization\Accommodation;
use App\Entity\Organization\Device;
use App\Entity\Organization\Service;
use App\Entity\Organization\SubService;
use App\Form\Type\LocationType;
use App\Form\Utils\Choices;
use App\Repository\Organization\DeviceRepository;
use App\Repository\Organization\ServiceRepository;
use App\Repository\Organization\SubServiceRepository;
use App\Security\CurrentUserService;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
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
            'attr' => ['placeholder' => 'accommodation.name.placeholder'],
            'help' => 'accommodation.name.help',
        ])
        ->add('service', EntityType::class, [
            'class' => Service::class,
            'choice_label' => 'name',
            'query_builder' => function (ServiceRepository $repo) {
                return $repo->getServicesOfUserQueryList($this->currentUser);
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
            'help' => 'accommodation.type.help',
            'required' => false,
        ])
        ->add('configuration', ChoiceType::class, [
            'choices' => Choices::getChoices(Accommodation::CONFIGURATION),
            'placeholder' => 'placeholder.select',
            'help' => 'accommodation.configuration.help',
            'required' => false,
        ])
        ->add('individualCollective', ChoiceType::class, [
            'choices' => Choices::getChoices(Accommodation::INDIVIDUAL_COLLECTIVE),
            'placeholder' => 'placeholder.select',
            'required' => false,
        ])
        ->add('area', IntegerType::class, [
            'help' => 'accommodation.area.help',
            'required' => false,
        ])
        ->add('lessor')
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
                    'seachLabel' => 'accommodation.location.placeholder',
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
                        return $repo->getSubServicesOfUserQueryList($this->currentUser);
                    },
                    'placeholder' => 'placeholder.select',
                    'required' => false,
                ])
                ->add('device', EntityType::class, [
                'class' => Device::class,
                'choice_label' => 'name',
                'query_builder' => function (DeviceRepository $repo) use ($accommodation) {
                    return $repo->getDevicesOfServiceQueryList($accommodation->getService());
                },
                'placeholder' => 'placeholder.select',
                'required' => false,
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
