<?php

namespace App\Form\Type;

use App\Entity\User;
use App\Entity\Device;
use App\Entity\Service;
use App\Repository\UserRepository;
use App\Repository\DeviceRepository;
use App\Security\CurrentUserService;
use App\Repository\ServiceRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
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
        $builder
            ->add('referents', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'fullname',
                'multiple' => true,
                'query_builder' => function (UserRepository $repo) {
                    return $repo->getAllUsersFromServicesQueryList($this->currentUser);
                },
                'label_attr' => ['class' => 'sr-only'],
                'placeholder' => '-- Référent --',
                'attr' => [
                    'class' => 'multi-select js-referent w-min-150 w-max-180',
                ],
                'required' => false,
            ])
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
                    'class' => 'multi-select js-service w-min-150 w-max-180',
                ],
                'required' => false,
            ])
            ->add('devices', EntityType::class, [
                'class' => Device::class,
                'choice_label' => 'name',
                'multiple' => true,
                'query_builder' => function (DeviceRepository $repo) {
                    return $repo->getDevicesFromUserQueryList($this->currentUser);
                },
                'label_attr' => ['class' => 'sr-only'],
                'placeholder' => '-- Device --',
                'attr' => [
                    'class' => 'multi-select js-device w-min-150 w-max-180',
                ],
                'required' => false,
            ]);
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
