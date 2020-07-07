<?php

namespace App\Form\Person;

use App\Entity\Person;
use App\Form\Model\PersonSearch;
use App\Form\Utils\Choices;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PersonSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('lastname', null, [
                'label_attr' => [
                    'class' => 'sr-only',
                ],
                'attr' => [
                    'class' => 'w-max-140 text-uppercase',
                    'placeholder' => 'Nom',
                    'autocomplete' => 'off',
                ],
            ])
            ->add('firstname', null, [
                'label_attr' => [
                    'class' => 'sr-only',
                ],
                'attr' => [
                    'class' => 'w-max-140 text-capitalize',
                    'placeholder' => 'Prénom',
                    'autocomplete' => 'off',
                ],
            ])
            ->add('birthdate', DateType::class, [
                'label_attr' => [
                    'class' => 'sr-only',
                ],
                'widget' => 'single_text',
                // "html5" => false,
                // "format" => "dd/MM/yyyy",
                'attr' => [
                    'class' => 'w-max-180',
                    'autocomplete' => 'off',
                ],
                'required' => false,
            ])
            ->add('phone', null, [
                'label_attr' => [
                    'class' => 'sr-only',
                ],
                'attr' => [
                    'placeholder' => 'Phone',
                    'class' => 'js-phone w-max-140',
                    'autocomplete' => 'off',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PersonSearch::class,
            'method' => 'get',
            'translation_domain' => 'forms',
            'allow_extra_fields' => true,
            'csrf_protection' => false,
        ]);
    }

    public function getBlockPrefix()
    {
        return '';
    }
}
