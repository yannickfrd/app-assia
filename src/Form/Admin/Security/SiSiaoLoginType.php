<?php

namespace App\Form\Admin\Security;

use App\Form\Model\SiSiao\SiSiaoLogin;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SiSiaoLoginType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', null, [
                'label_attr' => ['class' => 'sr-only'],
                'attr' => ['placeholder' => 'si_siao.username'],
            ])
            ->add('password', PasswordType::class, [
                'label_attr' => ['class' => 'sr-only'],
                'attr' => ['placeholder' => 'si_siao.password'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SiSiaoLogin::class,
            'translation_domain' => 'forms',
        ]);
    }
}
