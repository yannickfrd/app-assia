<?php

namespace App\Form\Organization\SubService;

use App\Entity\Organization\SubService;
use App\Entity\Organization\User;
use App\Repository\Organization\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SubServiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', null, [
                'attr' => [
                    'placeholder' => 'sub_service.name',
                ],
            ])
            ->add('phone1', null, [
                'attr' => [
                    'data-phone' => 'true',
                ],
            ])
            ->add('email', null, [
                'attr' => [
                    'placeholder' => 'Email',
                ],
            ])
            ->add('chief', EntityType::class, [
                'label' => 'sub_service.chief',
                'class' => User::class,
                'choice_label' => 'fullname',
                'query_builder' => function (UserRepository $repo) {
                    return $repo->createQueryBuilder('u')
                        ->where('u.disabledAt IS NULL')
                        ->andWhere('u.status IN (:status)')
                        ->setParameter('status', [
                            User::STATUS_COORDO,
                            User::STATUS_CHIEF,
                        ])
                        ->orderBy('u.lastname', 'ASC');
                },
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('comment', null, [
                'attr' => [
                    'rows' => 5,
                    'class' => 'justify',
                    'placeholder' => 'service.comment.placeholder',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SubService::class,
            'translation_domain' => 'forms',
        ]);
    }
}
