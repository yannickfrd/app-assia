<?php

namespace App\Form\Service;

use App\Entity\Pole;
use App\Entity\User;
use App\Entity\Service;
use App\Form\Utils\Choices;
use App\Form\Type\LocationType;
use App\Repository\UserRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class ServiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', null, [
                'attr' => [
                    'placeholder' => 'service.name',
                ],
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
                        ->andWhere('u.status = 2 OR u.status = 3  OR u.status = 4')
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
            // ->add('preAdmission', CheckBoxType::class, [
            //     'label_attr' => ['class' => 'custom-control-label'],
            //     'attr' => ['class' => 'custom-control-input checkbox'],
            //     'required' => false,
            // ])
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
            ->add('location', LocationType::class, [
                'data_class' => Service::class,
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
