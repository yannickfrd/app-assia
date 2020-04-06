<?php

namespace App\Form\User;

use App\Form\Model\UserChangeInfo;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserChangeInfoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', null, [
                'attr' => [
                    'placeholder' => 'Email',
                ],
            ])
            ->add('phone', null, [
                'attr' => [
                    'class' => 'js-phone',
                ],
            ])
            ->add('phone2', null, [
                'attr' => [
                    'class' => 'js-phone',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => UserChangeInfo::class,
            'translation_domain' => 'forms',
        ]);
    }
}
