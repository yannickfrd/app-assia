<?php

namespace App\Form\Support;

use App\Entity\User;
use App\Entity\Device;
use App\Entity\Service;
use App\Form\Utils\Choices;
use App\Entity\SupportGroup;
use App\Entity\Accommodation;
use App\Form\Type\LocationType;
use App\Repository\UserRepository;
use App\Repository\DeviceRepository;
use App\Security\CurrentUserService;
use App\Repository\ServiceRepository;
use Symfony\Component\Form\AbstractType;
use App\Form\OriginRequest\OriginRequestType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class SupportGroupType extends AbstractType
{
    private $currentUser;

    public function __construct(CurrentUserService $currentUser)
    {
        $this->currentUser = $currentUser;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $supportGroup = $options['data'];
        $service = $supportGroup->getService();

        $referentQueryBuilder = function (UserRepository $repo) use ($service, $supportGroup) {
            return $repo->getUsersQueryList($service, $supportGroup->getReferent());
        };

        $builder
            ->add('service', EntityType::class, [
                'class' => Service::class,
                'choice_label' => 'name',
                'query_builder' => function (ServiceRepository $repo) {
                    return $repo->getServicesFromUserQueryList($this->currentUser);
                },
                'attr' => [
                    'readonly' => true,
                ],
                'placeholder' => 'placeholder.select',
            ])
            ->add('device', EntityType::class, [
                'class' => Device::class,
                'choice_label' => 'name',
                'query_builder' => function (DeviceRepository $repo) use ($service) {
                    return $repo->getDevicesFromUserQueryList($this->currentUser, $service);
                },
                'placeholder' => 'placeholder.select',
            ])
            ->add('status', ChoiceType::class, [
                'choices' => Choices::getChoices(SupportGroup::STATUS),
                'placeholder' => 'placeholder.select',
            ])
            ->add('originRequest', OriginRequestType::class, [
                'attr' => [
                    'serviceId' => $service->getId(),
                ],
            ])
            ->add('startDate', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('referent', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'fullname',
                'query_builder' => $referentQueryBuilder,
                'placeholder' => 'placeholder.select',
            ])
            ->add('referent2', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'fullname',
                'query_builder' => $referentQueryBuilder,
                'placeholder' => 'placeholder.select',
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
            ->add('endAccommodation', CheckboxType::class, [
                'label_attr' => [
                    'class' => 'custom-control-label',
                ],
                'attr' => [
                    'class' => 'custom-control-input checkbox',
                ],
                'required' => false,
                'help' => 'endAccommodation.help',
            ])
            ->add('endStatusComment')
            ->add('agreement', CheckboxType::class, [
                'required' => true,
                'label_attr' => [
                    'class' => 'custom-control-label',
                ],
                'attr' => [
                    'class' => 'custom-control-input checkbox',
                ],
            ])
            ->add('supportPeople', CollectionType::class, [
                'entry_type' => SupportPersonType::class,
                'label_attr' => [
                    'class' => 'sr-only',
                ],
                'allow_add' => true,
                'allow_delete' => true,
                'delete_empty' => true,
                'required' => false,
            ])
            ->add('location', LocationType::class, [
                'data_class' => Accommodation::class,
                'attr' => [
                    // 'seachLabel' => 'Adresse du suivi',
                    'seachHelp' => 'Adresse du logement, hébergement, domiciliation...',
                ],
            ])
            ->add('comment', null, [
                'attr' => [
                    'rows' => 5,
                    'placeholder' => 'comment.placeholder',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SupportGroup::class,
            'translation_domain' => 'forms',
            'allow_extra_fields' => true,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'support';
    }
}
