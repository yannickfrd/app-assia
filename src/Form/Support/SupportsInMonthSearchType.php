<?php

namespace App\Form\Support;

use App\Form\Model\SupportsInMonthSearch;
use App\Form\Type\ServiceSearchType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SupportsInMonthSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
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
