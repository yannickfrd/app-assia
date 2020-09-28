<?php

namespace App\Form\Type;

use App\Entity\Device;
use App\Entity\Service;
use App\Entity\SubService;
use App\Entity\User;
use App\Repository\DeviceRepository;
use App\Repository\ServiceRepository;
use App\Repository\SubServiceRepository;
use App\Repository\UserRepository;
use App\Security\CurrentUserService;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ServiceSearchType extends AbstractType
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
                        return $repo->getServicesFromUserQueryList($this->currentUser);
                    },
                    'label_attr' => ['class' => 'sr-only'],
                    'placeholder' => '-- Service --',
                    'attr' => [
                        'class' => 'multi-select w-min-150 w-max-180',
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
                        return $repo->getSubServicesFromUserQueryList($this->currentUser, $serviceId);
                    },
                    'label_attr' => ['class' => 'sr-only'],
                    'placeholder' => '-- Sous-service --',
                    'attr' => [
                        'class' => 'multi-select w-min-150 w-max-180',
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
                        return $repo->getDevicesFromUserQueryList($this->currentUser, $serviceId);
                    },
                    'label_attr' => ['class' => 'sr-only'],
                    'placeholder' => '-- Device --',
                    'attr' => [
                        'class' => 'multi-select w-min-150 w-max-180',
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
                        return $repo->getAllUsersFromServicesQueryList($this->currentUser, $serviceId);
                    },
                    'label_attr' => ['class' => 'sr-only'],
                    'placeholder' => '-- Référent --',
                    'attr' => [
                        'class' => 'multi-select w-min-150 w-max-180',
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
