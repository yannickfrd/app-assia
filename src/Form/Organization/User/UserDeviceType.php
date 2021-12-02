<?php

namespace App\Form\Organization\User;

use App\Entity\Organization\Device;
use App\Entity\Organization\UserDevice;
use App\Repository\Organization\DeviceRepository;
use App\Security\CurrentUserService;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserDeviceType extends AbstractType
{
    private $currentUser;

    public function __construct(CurrentUserService $currentUser)
    {
        $this->currentUser = $currentUser;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('device', EntityType::class, [
                'class' => Device::class,
                'choice_label' => 'name',
                'query_builder' => function (DeviceRepository $repo) {
                    return $repo->getDevicesOfUserQueryBuilder($this->currentUser);
                },
                'placeholder' => 'placeholder.device',
                'attr' => [
                    'class' => 'col-auto mr-1 w-min-200',
                ],
            ])
            ->add('nbSupports', null, [
                'attr' => [
                    'class' => 'col-auto mr-1',
                    'placeholder' => 'placeholder.nbSupports',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UserDevice::class,
            'translation_domain' => 'forms',
        ]);
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}
