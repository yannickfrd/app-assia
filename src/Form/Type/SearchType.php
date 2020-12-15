<?php

namespace App\Form\Type;

use App\Entity\Organization\Device;
use App\Entity\Organization\Service;
use App\Entity\Organization\SubService;
use App\Entity\Organization\User;
use App\Repository\Organization\DeviceRepository;
use App\Repository\Organization\ServiceRepository;
use App\Repository\Organization\SubServiceRepository;
use App\Repository\Organization\UserRepository;
use App\Security\CurrentUserService;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchType extends AbstractType
{
    private $currentUser;

    public function __construct(CurrentUserService $currentUser)
    {
        $this->currentUser = $currentUser;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $attr = $builder->getOption('attr');
        $attrOptions = $attr['options'] ?? null;
        $serviceId = $attr['serviceId'] ?? null;

        if (null == $attrOptions || in_array('services', $attrOptions)) {
            $builder
                ->add('services', EntityType::class, [
                    'class' => Service::class,
                    'choice_label' => 'name',
                    'multiple' => true,
                    'query_builder' => function (ServiceRepository $repo) {
                        return $repo->getServicesOfUserQueryList($this->currentUser);
                    },
                    'placeholder' => 'placeholder.service',
                    'attr' => [
                        'class' => 'multi-select w-min-150 w-max-200',
                        'data-select2-id' => 'services',
                    ],
                    'required' => false,
                ]);
        }

        if (null == $attrOptions || in_array('subServices', $attrOptions)) {
            $builder
                ->add('subServices', EntityType::class, [
                    'class' => SubService::class,
                    'choice_label' => 'name',
                    'multiple' => true,
                    'query_builder' => function (SubServiceRepository $repo) use ($serviceId) {
                        return $repo->getSubServicesOfUserQueryList($this->currentUser, $serviceId);
                    },
                    'placeholder' => 'placeholder.subService',
                    'attr' => [
                        'class' => 'multi-select w-min-160 w-max-200',
                        'data-select2-id' => 'sub-services',
                    ],
                    'required' => false,
                ]);
        }

        if (null == $attrOptions || in_array('devices', $attrOptions)) {
            $builder
                ->add('devices', EntityType::class, [
                    'class' => Device::class,
                    'choice_label' => 'name',
                    'multiple' => true,
                    'query_builder' => function (DeviceRepository $repo) use ($serviceId) {
                        return $repo->getDevicesOfUserQueryList($this->currentUser, $serviceId);
                    },
                    'placeholder' => 'placeholder.device',
                    'attr' => [
                        'class' => 'multi-select w-min-150 w-max-200',
                        'data-select2-id' => 'devices',
                    ],
                    'required' => false,
                ]);
        }

        if (null == $attrOptions || in_array('referents', $attrOptions)) {
            $builder
                ->add('referents', EntityType::class, [
                    'class' => User::class,
                    'choice_label' => 'fullname',
                    'multiple' => true,
                    'query_builder' => function (UserRepository $repo) use ($serviceId) {
                        return $repo->getAllUsersOfServicesQueryList($this->currentUser, $serviceId);
                    },
                    'placeholder' => 'placeholder.referent',
                    'attr' => [
                        'class' => 'multi-select w-min-150 w-max-220',
                        'data-select2-id' => 'referents',
                    ],
                    'required' => false,
                ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'inherit_data' => true,
        ]);
    }

    public function getBlockPrefix()
    {
        return '';
    }
}
