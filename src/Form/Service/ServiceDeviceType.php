<?php

namespace App\Form\Service;

use App\Entity\Device;
use App\Entity\ServiceDevice;
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
