<?php

namespace App\Form\People\Person;

use App\Form\Model\People\PersonSearch;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PersonSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('lastname', null, [
            'label_attr' => ['class' => 'sr-only'],
            'attr' => [
                'class' => 'w-max-140 text-uppercase',
                'placeholder' => 'Lastname',
                'autocomplete' => 'off',
            ],
            ])
        ->add('firstname', null, [
            'label_attr' => ['class' => 'sr-only'],
            'attr' => [
                'class' => 'w-max-140 text-capitalize',
                'placeholder' => 'Firstname',
                'autocomplete' => 'off',
            ],
            ])
        ->add('birthdate', DateType::class, [
            'label_attr' => ['class' => 'sr-only'],
            'widget' => 'single_text',
            // "html5" => false,
            // "format" => "dd/MM/yyyy",
            'attr' => [
                'class' => 'w-max-180',
                'autocomplete' => 'off',
            ],
            'required' => false,
            ])
        ->add('siSiaoId', null, [
            'label_attr' => ['class' => 'sr-only'],
            'attr' => [
                'class' => 'w-max-160',
                'data-mask-type' => 'number',
                'placeholder' => 'si_siao_id.placeholder',
            ],
        ])
        ->add('siSiaoSearch', CheckboxType::class, [
            'label_attr' => ['class' => 'custom-control-label'],
            'attr' => ['class' => 'custom-control-input checkbox'],
            'required' => false,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PersonSearch::class,
            'translation_domain' => 'forms',
            'csrf_protection' => false,
            'attr' => ['id' => 'people_search'],
            // 'method' => 'post',
            // 'allow_extra_fields' => true,
        ]);
    }

    public function getBlockPrefix()
    {
        return '';
    }
}
