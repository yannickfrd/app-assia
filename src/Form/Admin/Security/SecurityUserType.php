<?php

namespace App\Form\Admin\Security;

use App\Entity\Organization\User;
use App\Form\Organization\Service\ServiceUserType;
use App\Form\Utils\Choices;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;

class SecurityUserType extends AbstractType
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstname', null, [
                'attr' => [
                    'class' => 'text-capitalize',
                    'placeholder' => 'Firstname',
                    'autocomplete' => 'off',
                ],
            ])
            ->add('lastname', null, [
                'attr' => [
                    'class' => 'text-capitalize',
                    'placeholder' => 'Lastname',
                    'autocomplete' => 'off',
                ],
            ])
            ->add('username', null, [
                'attr' => [
                    'placeholder' => 'Login',
                    'autocomplete' => 'off',
                ],
                'help' => 'user.username.help',
            ])
            ->add('email', null, [
                'attr' => ['placeholder' => 'Email'],
            ])
            ->add('phone1', null, [
                'attr' => [
                    'data-phone' => 'true',
                    'placeholder' => 'Phone',
                ],
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'user.status',
                'choices' => Choices::getChoices(User::STATUS),
                'placeholder' => 'placeholder.select',
                'required' => true,
            ])
            ->add('roles', ChoiceType::class, [
                'label' => 'user.roles',
                'choices' => $this->getRoles(),
                'multiple' => true,
                'attr' => [
                    'placeholder' => 'placeholder.select',
                    'size' => 1,
                ],
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $user = $event->getData();

            if ($user->getId()) {
                return;
            }

            $event->getForm()
                ->add('serviceUser', CollectionType::class, [
                    'entry_type' => ServiceUserType::class,
                    'allow_add' => true,
                    'allow_delete' => true,
                    'delete_empty' => true,
                    'prototype' => true,
                    'by_reference' => false,
                    'entry_options' => [
                        'attr' => ['class' => 'form-inline'],
                    ],
                ])
            ;
        });
    }

    public function getRoles(): array
    {
        if ($this->security->isGranted('ROLE_SUPER_ADMIN')) {
            return Choices::getChoices(User::ROLES);
        }

        return [
            'Utilisateur' => 'ROLE_USER',
            'Administrateur' => 'ROLE_ADMIN',
        ];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'translation_domain' => 'forms',
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'user';
    }
}
