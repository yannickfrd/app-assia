<?php

namespace App\Form\People\Person;

use App\Form\Model\People\PersonSearch;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PersonSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('lastname', SearchType::class, [
                'label_attr' => ['class' => 'sr-only'],
                'attr' => [
                    'class' => 'w-max-140 text-uppercase',
                    'placeholder' => 'Lastname',
                    'autocomplete' => 'off',
                ],
                'required' => false,
            ])
            ->add('firstname', SearchType::class, [
                'label_attr' => ['class' => 'sr-only'],
                'attr' => [
                    'class' => 'w-max-140 text-capitalize',
                    'placeholder' => 'Firstname',
                    'autocomplete' => 'off',
                ],
                'required' => false,
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
            ->add('siSiaoId', SearchType::class, [
                'label_attr' => ['class' => 'sr-only'],
                'attr' => [
                    'class' => 'w-min-180 w-max-180',
                    'data-mask-type' => 'number',
                    'placeholder' => 'si_siao_id.placeholder',
                ],
                'required' => false,
            ])
            ->add('siSiaoSearch', CheckboxType::class, [
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
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

    public function getBlockPrefix(): string
    {
        return '';
    }
}
