<?php

namespace App\Form\Support;

use App\Form\Type\ServiceSearchType;
use Symfony\Component\Form\AbstractType;
use App\Form\Model\SupportsInMonthSearch;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;

class SupportsInMonthSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            // ->add('date', DateType::class, [
            //     'widget' => 'choice',
            //     'placeholder' => [
            //         'year' => 'Year', 'month' => 'Month', 'day' => 'Day',
            //     ],
            //     'years' => range((int) date('Y') - 10, (int) date('Y')),
            //     'required' => false,
            // ])
            ->add('service', ServiceSearchType::class, [
                'data_class' => SupportsInMonthSearch::class,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SupportsInMonthSearch::class,
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
