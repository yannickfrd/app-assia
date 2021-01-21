<?php

namespace App\Form\Organization\Service;

use App\Entity\Organization\Device;
use App\Entity\Organization\ServiceDevice;
use App\Repository\Organization\DeviceRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ServiceDeviceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('device', EntityType::class, [
                'class' => Device::class,
                'choice_label' => 'name',
                'query_builder' => function (DeviceRepository $repo) {
                    return $repo->createQueryBuilder('d')
                        ->select('PARTIAL d.{id, name}')
                        ->orderBy('d.name', 'ASC');
                },
                'placeholder' => 'placeholder.select',
                'attr' => [
                    'class' => 'col-auto my-1',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ServiceDevice::class,
            'translation_domain' => 'forms',
        ]);
    }
}
