<?php

namespace App\Form\Support\Support;

use App\Entity\Organization\Device;
use App\Entity\Organization\Place;
use App\Entity\Organization\Service;
use App\Entity\Organization\SubService;
use App\Entity\Organization\User;
use App\Entity\Support\AsylumSupport;
use App\Entity\Support\Avdl;
use App\Entity\Support\HotelSupport;
use App\Entity\Support\PlaceGroup;
use App\Entity\Support\SupportGroup;
use App\Form\Organization\Place\PlaceGroupHotelType;
use App\Form\People\PeopleGroup\PeopleGroupSiSiaoType;
use App\Form\Support\Avdl\AvdlType;
use App\Form\Support\HotelSupport\HotelSupportType;
use App\Form\Support\OriginRequest\OriginRequestType;
use App\Form\Type\LocationType;
use App\Form\Utils\Choices;
use App\Repository\Organization\DeviceRepository;
use App\Repository\Organization\PlaceRepository;
use App\Repository\Organization\ServiceRepository;
use App\Repository\Organization\SubServiceRepository;
use App\Repository\Organization\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;

class SupportGroupType extends AbstractType
{
    /** @var User */
    private $user;

    public function __construct(Security $security)
    {
        $this->user = $security->getUser();
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('service', EntityType::class, [
                'class' => Service::class,
                'choice_label' => 'name',
                'query_builder' => function (ServiceRepository $repo) {
                    return $repo->getServicesOfUserQueryBuilder($this->user);
                },
                'placeholder' => 'placeholder.select',
            ])
            ->add('status', ChoiceType::class, [
                'choices' => Choices::getChoices(SupportGroup::STATUS),
                'placeholder' => 'placeholder.select',
            ])
            ->add('location', LocationType::class, [
                'data_class' => SupportGroup::class,
                'attr' => [
                    'geo_location' => true,
                    'location_search_help' => 'support.location_search.help',
                ],
            ])
            ->add('startDate', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('theoreticalEndDate', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('endDate', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('endStatus', ChoiceType::class, [
                'choices' => Choices::getChoices(SupportGroup::END_STATUS),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('endStatusComment')
            ->add('endPlace', CheckboxType::class, [
                'required' => false,
                'help' => 'endPlace.help',
            ])
            ->add('endLocation', LocationType::class, [
                'data_class' => SupportGroup::class,
                'attr' => [
                    'fullAddress' => 'endLocationFullAddress',
                    'address' => 'endLocationAddress',
                    'city' => 'endLocationCity',
                    'zipcode' => 'endLocationZipcode',
                    'comment' => false,
                    'location_search_label' => 'end_location',
                ],
            ])
            ->add('agreement', CheckboxType::class, [
                'required' => true,
            ])
            ->add('supportPeople', CollectionType::class, [
                'entry_type' => SupportPersonType::class,
                'label' => null,
                'allow_add' => true,
                'allow_delete' => true,
                'delete_empty' => true,
                'required' => false,
            ])
            ->add('comment', null, [
                'attr' => ['placeholder' => 'comment.placeholder'],
            ])
            ->add('_cloneSupport', HiddenType::class, [
                'mapped' => false,
            ])
            ->add('_siSiaoImport', HiddenType::class, [
                'mapped' => false,
            ])
        ;

        $formModifier = $this->formModifier();

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($formModifier) {
            $form = $event->getForm();
            $supportGroup = $event->getData();
            $service = $supportGroup->getService();
            $formModifier($form, $supportGroup);

            $this->addExtraFields($form, $service);
        });

        $builder->get('service')->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use ($formModifier) {
            $form = $event->getForm();
            $supportGroup = $form->getParent()->getData();

            $formModifier($form->getParent(), $supportGroup);
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SupportGroup::class,
            'translation_domain' => 'forms',
            'allow_extra_fields' => true,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'support';
    }

    private function formModifier(): \Closure
    {
        return function (FormInterface $form, SupportGroup $supportGroup) {
            $service = $supportGroup->getService();
            $device = $supportGroup->getDevice();
            $subService = $supportGroup->getSubService();

            $form
                ->add('subService', EntityType::class, [
                    'class' => SubService::class,
                    'choice_label' => 'name',
                    'query_builder' => function (SubServiceRepository $repo) use ($service) {
                        return $repo->getSubServicesOfUserQueryBuilder($this->user, $service);
                    },
                    'placeholder' => 'placeholder.select',
                    'required' => false,
                ])
                ->add('device', EntityType::class, [
                    'class' => Device::class,
                    'choice_value' => 'code',
                    'choice_label' => 'name',
                    'query_builder' => function (DeviceRepository $repo) use ($service) {
                        return $repo->getDevicesOfUserQueryBuilder($this->user, $service);
                    },
                    'placeholder' => 'placeholder.select',
                ])
                ->add('referent', EntityType::class, $optionsReferent = $this->optionsReferent($supportGroup))
                ->add('referent2', EntityType::class, $optionsReferent)
                ->add('originRequest', OriginRequestType::class, [
                    'attr' => ['service' => $service],
                ])
                ->add('supportPeople', CollectionType::class, [
                    'entry_type' => SupportPersonType::class,
                    'label' => null,
                    'allow_add' => true,
                    'allow_delete' => true,
                    'delete_empty' => true,
                    'required' => false,
                    'attr' => ['service' => $service],
                ])
            ;

            if ($service and Choices::YES === $service->getPlace()) {
                $form->add('_place', EntityType::class, [
                    'class' => Place::class,
                    'choice_label' => 'name',
                    'query_builder' => function (PlaceRepository $repo) use ($service, $subService) {
                        return $repo->getPlacesQueryBuilder($service, $subService);
                    },
                    'label' => 'place.name',
                    'attr' => ['autocomplete' => 'true'],
                    'placeholder' => 'placeholder.select',
                    'help' => 'placeGroup.help',
                    'mapped' => false,
                    'required' => false,
                ]);
            }
        };
    }

    private function addExtraFields(FormInterface $form, Service $service): void
    {
        $form
            ->add('endReason', ChoiceType::class, [
                'choices' => Choices::getChoices($this->getEndReasonChoices($service)),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
        ;

        switch ($service->getType()) {
            case Service::SERVICE_TYPE_AVDL:
                $this->addAvdlFields($form);
                break;
            case Service::SERVICE_TYPE_HOTEL:
                $this->addHotelFields($form);
                break;
        }
    }

    private function getEndReasonChoices(Service $service): array
    {
        switch ($service->getType()) {
            case Service::SERVICE_TYPE_AVDL:
                return Avdl::END_REASONS;
            case Service::SERVICE_TYPE_HOTEL:
                return HotelSupport::END_REASONS;
            case Service::SERVICE_TYPE_ASYLUM:
                return AsylumSupport::END_REASONS;
            default:
                return SupportGroup::REGULAR_END_REASONS;
        }
    }

    private function addAvdlFields(FormInterface $form): void
    {
        $form
            ->remove('startDate')
            ->remove('endDate')
            ->remove('endStatusComment')
            ->add('avdl', AvdlType::class)
        ;
    }

    private function addHotelFields(FormInterface $form): void
    {
        /** @var SupportGroup */
        $supportGroup = $form->getConfig()->getData();

        $form
            ->remove('location')
            ->add('status', ChoiceType::class, [
                'choices' => Choices::getChoices(HotelSupport::STATUS),
                'placeholder' => 'placeholder.select',
                'required' => true,
            ])
            ->add('peopleGroup', PeopleGroupSiSiaoType::class, [
                'data' => $supportGroup->getPeopleGroup(),
            ])
            ->add('hotelSupport', HotelSupportType::class)
        ;

        if (0 === $supportGroup->getPlaceGroups()->count()) {
            $this->addPlaceGroup($supportGroup);
        }

        $form->add('placeGroups', CollectionType::class, [
            'entry_type' => PlaceGroupHotelType::class,
            'label' => null,
            'allow_add' => false,
            'allow_delete' => false,
            'delete_empty' => true,
            'attr' => [
                'service' => $supportGroup->getService(),
                'subService' => $supportGroup->getSubService(),
            ],
            'required' => true,
        ]);
    }

    private function addPlaceGroup(SupportGroup $supportGroup): void
    {
        $placeGroup = (new PlaceGroup())->setPeopleGroup($supportGroup->getPeopleGroup());
        $supportGroup->addPlaceGroup($placeGroup);
    }

    /**
     * Retourne les options du champ Intervenant.
     */
    private function optionsReferent(SupportGroup $supportGroup): array
    {
        return [
            'class' => User::class,
            'choice_label' => 'fullname',
            'query_builder' => function (UserRepository $repo) use ($supportGroup) {
                return $repo->getSupportReferentsQueryBuilder(
                    $supportGroup->getService(),
                    $this->user,
                    $supportGroup->getReferent()
                );
            },
             'attr' => ['autocomplete' => 'true'],
            'placeholder' => 'placeholder.select',
            'required' => false,
        ];
    }
}
