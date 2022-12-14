<?php

namespace App\Form\Support\Support;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DeletedSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('deleted', CheckboxType::class, [
                'label' => 'support.label.soft_deleteable',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'inherit_data' => true,
            'allow_extra_fields' => true,
            'translation_domain' => 'forms',
        ]);
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}
