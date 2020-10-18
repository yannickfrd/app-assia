<?php

namespace App\Form\Person;

use App\Entity\Person;
use App\Form\Utils\Choices;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PersonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('lastname', null, [
                'attr' => [
                    'class' => 'text-uppercase',
                    'placeholder' => 'Lastname',
                ],
            ])
            ->add('firstname', null, [
                'attr' => [
                    'class' => 'text-capitalize',
                    'placeholder' => 'Firstname',
                ],
            ])
            ->add('usename', null, [
                'label' => 'Usename or maiden name',
                'attr' => [
                    'class' => 'text-capitalize',
                ],
            ])
            ->add('birthdate', DateType::class, [
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'col-md-12',
                ],
                'required' => true,
            ])
            ->add('gender', ChoiceType::class, [
                'attr' => [
                    'class' => 'col-md-12',
                ],
                'choices' => Choices::getchoices(Person::GENDER),
                'placeholder' => 'placeholder.select',
                'required' => true,
            ])
            ->add('phone1', null, [
                'attr' => [
                    'class' => 'js-phone',
                ],
            ])
            ->add('phone2', null, [
                'attr' => [
                    'class' => 'js-phone',
                ],
            ])
            ->add('email')
            ->add('contactOtherPerson', null, [
                'help' => "Autre personne à contacter en cas d'urgence.",
            ])
            ->add('comment', null, [
                'attr' => [
                    'rows' => 5,
                    'placeholder' => 'person.comment',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Person::class,
            'translation_domain' => 'forms',
        ]);
    }
}
