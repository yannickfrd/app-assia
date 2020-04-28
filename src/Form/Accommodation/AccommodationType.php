<?php

namespace App\Form\Accommodation;

use App\Entity\Device;
use App\Entity\Service;
use App\Form\Utils\Choices;
use App\Entity\Accommodation;
use App\Form\Type\LocationType;
use App\Repository\DeviceRepository;
use App\Security\CurrentUserService;
use App\Repository\ServiceRepository;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

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
            'placeholder' => '-- Select --',
        ])
        ->add('placesNumber')
        ->add('openingDate', DateType::class, [
            'widget' => 'single_text',
        ])
        ->add('closingDate', DateType::class, [
            'widget' => 'single_text',
            'required' => false,
        ])
        ->add('accommodationType', ChoiceType::class, [
            'choices' => Choices::getChoices(Accommodation::ACCOMMODATION_TYPE),
            'placeholder' => '-- Select --',
            'help' => 'Chambre, T1, T2, T3...',
            'required' => false,
        ])
        ->add('configuration', ChoiceType::class, [
            'choices' => Choices::getChoices(Accommodation::CONFIGURATION),
            'placeholder' => '-- Select --',
            'help' => 'Diffus ou regroupé',
            'required' => false,
        ])
        ->add('individualCollective', ChoiceType::class, [
            'choices' => Choices::getChoices(Accommodation::INDIVIDUAL_COLLECTIVE),
            'placeholder' => '-- Select --',
            'required' => false,
        ])
        ->add('comment', null, [
            'attr' => [
                'rows' => 5,
                'placeholder' => 'Description...',
            ],
        ])
        ->add('location', LocationType::class, [
            'data_class' => Accommodation::class,
        ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $accommodation = $event->getData();

            $event->getForm()->add('device', EntityType::class, [
                'class' => Device::class,
                'choice_label' => 'name',
                'query_builder' => function (DeviceRepository $repo) use ($accommodation) {
                    return $repo->getDevicesFromServiceQueryList($accommodation);
                },
                'placeholder' => '-- Select --',
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
