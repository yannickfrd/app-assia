<?php

namespace App\Form\Support\Rdv;

use App\Form\Model\Support\SupportRdvSearch;
use App\Form\Type\DateSearchType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SupportRdvSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', null, [
                'label_attr' => ['class' => 'sr-only'],
                'attr' => [
                    'placeholder' => 'Title',
                    'class' => 'w-max-170',
                ],
            ])
            ->add('date', DateSearchType::class, [
                'data_class' => SupportRdvSearch::class,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SupportRdvSearch::class,
            'method' => 'get',
            'translation_domain' => 'forms',
            'allow_extra_fields' => true,
            'csrf_protection' => false,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'search';
    }
}
