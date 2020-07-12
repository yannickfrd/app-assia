<?php

namespace App\Form\Rdv;

use App\Entity\Rdv;
use App\Form\Utils\Choices;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RdvType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', null, [
                'attr' => [
                    'class' => 'font-weight-bold',
                    'placeholder' => 'rdv.placeholder.title',
                ],
            ])
            ->add('start', DateTimeType::class, [
                'widget' => 'single_text',
            ])
            ->add('end', DateTimeType::class, [
                'widget' => 'single_text',
            ])
            ->add('status', ChoiceType::class, [
                'choices' => Choices::getchoices(Rdv::STATUS),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('location', null, [
                'attr' => [
                    'class' => 'js-search',
                    'placeholder' => 'rdv.placeholder.location',
                    'autocomplete' => 'off',
                ],
            ])
            ->add('content', null, [
                'attr' => [
                    // "class" => "d-none",
                    'rows' => 5,
                    'placeholder' => 'rdv.placeholder.content',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Rdv::class,
            'translation_domain' => 'forms',
        ]);
    }
}
