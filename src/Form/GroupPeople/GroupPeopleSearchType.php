<?php

namespace App\Form\GroupPeople;

use App\Entity\GroupPeople;
use App\Form\Model\GroupPeopleSearch;
use App\Form\Utils\Choices;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GroupPeopleSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('lastname', null, [
                'label_attr' => [
                    'class' => 'sr-only',
                ],
                'attr' => [
                    'placeholder' => 'Lastname',
                    'class' => 'w-max-180 text-uppercase',
                    'autocomplete' => 'off',
                ],
            ])
            ->add('firstname', null, [
                'label_attr' => [
                    'class' => 'sr-only',
                ],
                'attr' => [
                    'placeholder' => 'Firstname',
                    'class' => 'w-max-140 text-capitalize',
                    'autocomplete' => 'off',
                ],
            ])
            ->add('birthdate', DateType::class, [
                'label_attr' => [
                    'class' => 'sr-only',
                ],
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'w-max-180',
                    'autocomplete' => 'off',
                ],
                'required' => false,
            ])
            ->add('head', CheckBoxType::class, [
                'label' => 'DP',
                'required' => false,
                'label_attr' => [
                    'class' => 'custom-control-label',
                ],
                'attr' => [
                    'class' => 'custom-control-input checkbox',
                ],
            ])
            ->add('familyTypology', ChoiceType::class, [
                'placeholder' => '-- Family Typology --',
                'label_attr' => [
                    'class' => 'sr-only',
                ],
                'required' => false,
                'choices' => Choices::getChoices(GroupPeople::FAMILY_TYPOLOGY),
                'attr' => [
                    'class' => 'w-max-200',
                    'autocomplete' => 'off',
                ],
            ])
            ->add('nbPeople', null, [
                'label_attr' => [
                    'class' => 'sr-only',
                ],
                'attr' => [
                    'placeholder' => 'NbPeople',
                    'class' => 'w-max-100',
                    'autocomplete' => 'off',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => GroupPeopleSearch::class,
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
