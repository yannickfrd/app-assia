<?php

namespace App\Form\Accommodation;

use App\Entity\AccommodationGroup;
use App\Entity\AccommodationPerson;
use App\Form\Utils\Choices;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AccommodationPersonType extends AbstractType
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
                'choices' => Choices::getChoices(AccommodationGroup::END_REASON),
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
            'data_class' => AccommodationPerson::class,
            'translation_domain' => 'forms',
        ]);
    }
}
