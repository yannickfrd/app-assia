<?php

namespace App\Form\Organization\User;

use App\Entity\Organization\User;
use App\Form\Model\Security\UserChangeInfo;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserChangeInfoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /**
         * @var User $user
         */
        $user = $options['user'];

        $builder
            ->add('firstname', null, [
                'data' => $user->getFirstname(),
                'disabled' => true,
                'mapped' => false,
            ])
            ->add('lastname', null, [
                'data' => $user->getLastname(),
                'disabled' => true,
                'mapped' => false,
            ])
            ->add('username', null, [
                'data' => $user->getUsername(),
                'disabled' => true,
                'mapped' => false,
            ])
            ->add('status', null, [
                'data' => $user->getStatusToString(),
                'disabled' => true,
                'mapped' => false,
            ])
            ->add('email', null, [
                'attr' => ['placeholder' => 'Email'],
            ])
            ->add('phone1', null, [
                'attr' => ['data-phone' => 'on'],
            ])
            ->add('phone2', null, [
                'attr' => ['data-phone' => 'on'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UserChangeInfo::class,
            'translation_domain' => 'forms',
            'user' => User::class,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'user';
    }
}
