<?php

namespace App\Form\SubService;

use App\Entity\SubService;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SubServiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', null, [
                'attr' => [
                    'placeholder' => 'service.name',
                ],
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
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SubService::class,
            'translation_domain' => 'forms',
        ]);
    }
}