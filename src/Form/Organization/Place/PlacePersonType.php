<?php

namespace App\Form\Organization\Place;

use App\Entity\Support\PlaceGroup;
use App\Entity\Support\PlacePerson;
use App\Form\Utils\Choices;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PlacePersonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('startDate', DateType::class, [
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'w-max-165',
                ],
            ])
            ->add('endDate', DateType::class, [
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'w-max-165',
                ],
                'required' => false,
            ])
            ->add('endReason', ChoiceType::class, [
                'choices' => Choices::getChoices(PlaceGroup::END_REASON),
                'attr' => ['class' => 'w-min-200'],
                'required' => false,
                'placeholder' => 'placeholder.select',
            ])
            ->add('commentEndReason', null, [
                'attr' => [
                    'rows' => 1,
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PlacePerson::class,
            'translation_domain' => 'forms',
        ]);
    }
}
