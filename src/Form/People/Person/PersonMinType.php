<?php

namespace App\Form\People\Person;

use App\Entity\People\Person;
use App\Form\Utils\Choices;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PersonMinType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstname', null, [
                'attr' => [
                    'class' => 'text-capitalize',
                    'placeholder' => 'Firstname',
                ],
            ])
            ->add('lastname', null, [
                'attr' => [
                    'class' => 'w-min-150 text-uppercase',
                    'placeholder' => 'Lastname',
                ],
            ])
            ->add('birthdate', DateType::class, [
                'widget' => 'single_text',
                'attr' => ['autocomplete' => 'off'],
            ])
            ->add('gender', ChoiceType::class, [
                'attr' => [
                    'class' => 'w-min-150',
                ],
                'choices' => Choices::getChoices(Person::GENDERS),
                'placeholder' => 'placeholder.select',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Person::class,
            'translation_domain' => 'forms',
        ]);
    }
}
