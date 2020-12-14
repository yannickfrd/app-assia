<?php

namespace App\Form\Evaluation;

use App\Entity\Evaluation\EvalHotelLifeGroup;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EvalHotelLifeGroupType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('food', null, [
                'attr' => [
                        'rows' => 5,
                        'class' => 'justify',
                ],
        ])
        ->add('clothing', null, [
                'attr' => [
                        'rows' => 5,
                        'class' => 'justify',
                ],
        ])
        ->add('roomMaintenance', null, [
                'attr' => [
                        'rows' => 5,
                        'class' => 'justify',
                ],
        ])
        ->add('otherHotelLife', null, [
                'attr' => [
                        'rows' => 5,
                        'class' => 'justify',
                ],
        ])
        ->add('commentHotelLife', null, [
                'attr' => [
                        'rows' => 5,
                        'class' => 'justify',
                ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => EvalHotelLifeGroup::class,
            'translation_domain' => 'evaluation',
        ]);
    }
}
