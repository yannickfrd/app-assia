<?php

namespace App\Form\Organization\Service;

use App\Entity\Organization\Pole;
use App\Entity\Organization\Service;
use App\Entity\Organization\ServiceSetting;
use App\Entity\Organization\Tag;
use App\Entity\Organization\User;
use App\Form\Type\LocationType;
use App\Form\Utils\Choices;
use App\Repository\Organization\TagRepository;
use App\Repository\Organization\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ServiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
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
            ->add('type', ChoiceType::class, [
                'label' => 'service.type',
                'choices' => Choices::getChoices(Service::SERVICE_TYPE),
                'placeholder' => 'placeholder.select',
                'required' => false,
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
            ->add('place', ChoiceType::class, [
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
                'help' => 'contribution.rate.help',
            ])
            ->add('minRestToLive', MoneyType::class, [
                'attr' => [
                    'class' => 'text-right',
                    'placeholder' => 'Amount',
                ],
                'help' => 'service.minRestToLive.help',
                'required' => false,
            ])
            ->add('comment', null, [
                'attr' => [
                    'rows' => 5,
                    'class' => 'justify',
                    'placeholder' => 'service.comment.placeholder',
                ],
            ])
            ->add('coefficient', ChoiceType::class, [
                'label' => 'service.coefficient',
                'choices' => Choices::getChoices(Choices::YES_NO),
                'placeholder' => 'placeholder.select',
                'required' => false,
                'help' => 'service.coefficient.placeholder',
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
                'attr' => ['searchLabel' => 'Adresse du service'],
            ])
            ->add('setting', ServiceSettingType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Service::class,
            'translation_domain' => 'forms',
        ]);
    }
}
