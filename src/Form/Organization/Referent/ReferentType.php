<?php

namespace App\Form\Organization\Referent;

use App\Entity\Organization\Referent;
use App\Form\Type\LocationType;
use App\Form\Utils\Choices;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReferentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', null, [
                'label' => 'Service name',
                'attr' => [
                    'placeholder' => 'service.name',
                ],
            ])
            ->add('type', ChoiceType::class, [
                'choices' => Choices::getchoices(Referent::TYPE),
                'placeholder' => 'placeholder.select',
            ])
            ->add('socialWorker', null, [
                'attr' => [
                    'placeholder' => 'referent.socialWorker',
                ],
            ])
            ->add('socialWorker2', null, [
                'attr' => [
                    'placeholder' => 'referent.socialWorker2',
                ],
            ])
            ->add('email', null, [
                'attr' => [
                    'placeholder' => 'Email1',
                ],
            ])
            ->add('email2', null, [
                'attr' => [
                    'placeholder' => 'Email2',
                ],
            ])
            ->add('phone1', null, [
                'attr' => [
                    'class' => 'js-phone',
                    'placeholder' => 'Phone1',
                ],
            ])
            ->add('phone2', null, [
                'attr' => [
                    'class' => 'js-phone',
                    'placeholder' => 'Phone2',
                ],
            ])
            ->add('location', LocationType::class, [
                'data_class' => Referent::class,
                'attr' => ['seachLabel' => 'Adresse du service référent'],
            ])
            ->add('comment', null, [
                'attr' => [
                    'rows' => 4,
                    'placeholder' => 'referent.comment.plaholder',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Referent::class,
            'translation_domain' => 'forms',
        ]);
    }
}
