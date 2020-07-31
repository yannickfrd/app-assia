<?php

namespace App\Form\Referent;

use App\Entity\Referent;
use App\Form\Utils\Choices;
use App\Form\Type\LocationType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

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
                    'placeholder' => 'referent.socialWorker.placeholder',
                ],
            ])
            ->add('socialWorker2', null, [
                'attr' => [
                    'placeholder' => 'referent.socialWorker2.placeholder',
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
                'data' => ['seachLabel' => 'Adresse du service référent'],
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
