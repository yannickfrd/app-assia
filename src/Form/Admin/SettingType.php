<?php

namespace App\Form\Admin;

use App\Entity\Admin\Setting;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SettingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('organizationName', TextType::class, [
                'empty_data' => '',
                'required' => false,
            ])
            ->add('softDeletionDelay', IntegerType::class, [
                'help' => 'setting.soft_deletion_delay.help',
                'label' => 'setting.soft_deletion_delay.label',
                'attr' => [
                    'placeholder' => 'setting.soft_deletion_delay.placeholder',
                    'min' => 0,
                ],
                'required' => false,
            ])
            ->add('hardDeletionDelay', IntegerType::class, [
                'help' => 'setting.hard_deletion_delay.help',
                'label' => 'setting.hard_deletion_delay.label',
                'attr' => [
                    'placeholder' => 'setting.hard_deletion_delay.placeholder',
                    'min' => 0,
                ],
                'required' => false,
            ])
            ->add('weeklyAlert', CheckboxType::class, [
                'label_attr' => ['class' => 'custom-control-label'],
                'attr' => ['class' => 'custom-control-input checkbox'],
                'required' => false,
            ])
            ->add('dailyAlert', CheckboxType::class, [
                'label_attr' => ['class' => 'custom-control-label'],
                'attr' => ['class' => 'custom-control-input checkbox'],
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Setting::class,
            'translation_domain' => 'forms',
        ]);
    }
}
