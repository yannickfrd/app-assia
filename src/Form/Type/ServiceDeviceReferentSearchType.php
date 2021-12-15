<?php

namespace App\Form\Type;

use App\Entity\Organization\Device;
use App\Entity\Organization\Pole;
use App\Entity\Organization\Service;
use App\Entity\Organization\SubService;
use App\Entity\Organization\User;
use App\Repository\Organization\DeviceRepository;
use App\Repository\Organization\PoleRepository;
use App\Repository\Organization\ServiceRepository;
use App\Repository\Organization\SubServiceRepository;
use App\Repository\Organization\UserRepository;
use App\Security\CurrentUserService;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ServiceDeviceReferentSearchType extends AbstractType
{
    private $currentUser;

    public function __construct(CurrentUserService $currentUser)
    {
        $this->currentUser = $currentUser;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $dataClass = $builder->getDataClass();
        $attr = $builder->getOption('attr');
        $attrOptions = $attr['options'] ?? null;
        /** @var Service $service */
        $service = $attr['service'] ?? null;

        if ($this->currentUser->hasRole('ROLE_SUPER_ADMIN')) {
            $builder
                ->add('poles', EntityType::class, [
                    'class' => Pole::class,
                    'choice_label' => 'name',
                    'multiple' => true,
                    'query_builder' => function (PoleRepository $repo) {
                        return $repo->getPoleQueryBuilder();
                    },
                    'placeholder' => 'placeholder.pole',
                    'attr' => [
                        'class' => 'multi-select w-min-150 w-max-200',
                        'size' => 1,
                        'data-select2-id' => 'poles',
                    ],
                    'required' => false,
                ]);
        }

        if (null === $attrOptions || in_array('services', $attrOptions)) {
            $builder
                ->add('services', EntityType::class, [
                    'class' => Service::class,
                    'choice_label' => 'name',
                    'multiple' => true,
                    'query_builder' => function (ServiceRepository $repo) use ($dataClass) {
                        return $repo->getServicesOfUserQueryBuilder($this->currentUser, $dataClass);
                    },
                    'placeholder' => 'placeholder.service',
                    'attr' => [
                        'class' => 'multi-select w-min-150 w-max-200',
                        'size' => 1,
                        'data-select2-id' => 'services',
                    ],
                    'required' => false,
                ]);
        }

        if (null === $attrOptions || in_array('subServices', $attrOptions)) {
            $builder
                ->add('subServices', EntityType::class, [
                    'class' => SubService::class,
                    'choice_label' => 'name',
                    'multiple' => true,
                    'query_builder' => function (SubServiceRepository $repo) use ($service, $dataClass) {
                        return $repo->getSubServicesOfUserQueryBuilder($this->currentUser, $service, $dataClass);
                    },
                    'placeholder' => 'placeholder.subService',
                    'attr' => [
                        'class' => 'multi-select w-min-160 w-max-200',
                        'size' => 1,
                        'data-select2-id' => 'sub-services',
                    ],
                    'required' => false,
                ]);
        }

        if (null === $attrOptions || in_array('devices', $attrOptions)) {
            $builder
                ->add('devices', EntityType::class, [
                    'class' => Device::class,
                    'choice_label' => 'name',
                    'multiple' => true,
                    'query_builder' => function (DeviceRepository $repo) use ($service, $dataClass) {
                        return $repo->getDevicesOfUserQueryBuilder($this->currentUser, $service, $dataClass);
                    },
                    'placeholder' => 'placeholder.device',
                    'attr' => [
                        'class' => 'multi-select w-min-150 w-max-200',
                        'size' => 1,
                        'data-select2-id' => 'devices',
                    ],
                    'required' => false,
                ]);
        }

        if (null === $attrOptions || in_array('referents', $attrOptions)) {
            $builder
                ->add('referents', EntityType::class, [
                    'class' => User::class,
                    'choice_label' => 'fullname',
                    'multiple' => true,
                    'query_builder' => function (UserRepository $repo) use ($service, $dataClass) {
                        return $repo->getReferentsOfServicesQueryBuilder($this->currentUser, $service, $dataClass);
                    },
                    'placeholder' => 'placeholder.referent',
                    'attr' => [
                        'class' => 'multi-select w-min-150 w-max-220',
                        'size' => 1,
                        'data-select2-id' => 'referents',
                    ],
                    'required' => false,
                ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'inherit_data' => true,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}
