<?php

namespace App\Form\Service;

use App\Entity\Pole;
use App\Entity\Service;
use App\Entity\User;
use App\Form\Type\LocationType;
use App\Form\Utils\Choices;
use App\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ServiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', null, [
                'attr' => ['placeholder' => 'service.name'],
            ])
            ->add('pole', EntityType::class, [
                'class' => Pole::class,
                'choice_label' => 'name',
                'placeholder' => 'placeholder.select',
            ])
            ->add('phone1', null, [
                'attr' => [
                    'class' => 'js-phone',
                ],
            ])
            ->add('email', null, [
                'attr' => [
                    'placeholder' => 'Email',
                ],
            ])
            ->add('chief', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'fullname',
                'query_builder' => function (UserRepository $repo) {
                    return $repo->createQueryBuilder('u')
                        ->where('u.disabledAt IS NULL')
                        ->andWhere('u.status IN (:status)')
                        ->setParameter('status', [
                            User::STATUS_COORDO,
                            User::STATUS_CHIEF,
                            User::STATUS_DIRECTOR,
                        ])
                        ->orderBy('u.lastname', 'ASC');
                },
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('finessId')
            ->add('siretId')
            ->add('startDate', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Opening date',
                'required' => false,
            ])
            ->add('endDate', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Closing date',
                'required' => false,
            ])
            ->add('supportAccess', ChoiceType::class, [
                'choices' => Choices::getChoices(Service::SUPPORT_ACCESS),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('preAdmission', ChoiceType::class, [
                'choices' => Choices::getChoices(Choices::YES_NO),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('accommodation', ChoiceType::class, [
                'choices' => Choices::getChoices(Choices::YES_NO),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])

            ->add('justice', ChoiceType::class, [
                'choices' => Choices::getChoices(Choices::YES_NO),
                'label' => 'Justice activity',
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('contribution', ChoiceType::class, [
                'choices' => Choices::getChoices(Choices::YES_NO),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('contributionType', ChoiceType::class, [
                'choices' => Choices::getChoices(Service::CONTRIBUTION_TYPE),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('contributionRate', null, [
                'help' => 'Rate beetween 0 and 1.',
            ])
            ->add('comment', null, [
                'attr' => [
                    'rows' => 5,
                    'class' => 'justify',
                    'placeholder' => 'service.comment.placeholder',
                ],
            ])
            ->add('serviceDevices', CollectionType::class, [
                'entry_type' => ServiceDeviceType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'delete_empty' => true,
                'prototype' => true,
                'by_reference' => false,
                'label_attr' => [
                    'class' => 'sr-only',
                ],
                'entry_options' => [
                    'attr' => ['class' => 'form-inline'],
                ],
            ])
            // ->add('organizations', CollectionType::class, [
            //     'entry_type' => ServiceOrganizationType::class,
            //     'allow_add' => true,
            //     'allow_delete' => true,
            //     'delete_empty' => true,
            //     'prototype' => true,
            //     'by_reference' => false,
            //     'label_attr' => [
            //         'class' => 'sr-only',
            //     ],
            //     'entry_options' => [
            //         'attr' => ['class' => 'form-inline'],
            //     ],
            // ])
            ->add('location', LocationType::class, [
                'data_class' => Service::class,
                'attr' => ['seachLabel' => 'Adresse du service'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Service::class,
            'translation_domain' => 'forms',
        ]);
    }
}
