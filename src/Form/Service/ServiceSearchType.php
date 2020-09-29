<?php

namespace App\Form\Service;

use App\Entity\Pole;
use App\Form\Model\ServiceSearch;
use App\Form\Utils\Choices;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ServiceSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', null, [
                'label_attr' => [
                    'class' => 'sr-only',
                ],
                'attr' => [
                    'class' => 'w-max-200',
                    'placeholder' => 'Service name',
                    'autocomplete' => 'off',
                ],
            ])
            ->add('city', null, [
                'label_attr' => [
                    'class' => 'sr-only',
                ],
                'attr' => [
                    'class' => 'w-max-160',
                    'placeholder' => 'City',
                    'autocomplete' => 'off',
                ],
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
            ])
            ->add('pole', EntityType::class, [
                'class' => Pole::class,
                'choice_label' => 'name',
                'label_attr' => [
                    'class' => 'sr-only',
                ],
                'placeholder' => 'placeholder.pole',
                'required' => false,
            ])
            ->add('disabled', ChoiceType::class, [
                'label_attr' => ['class' => 'sr-only'],
                'choices' => Choices::getChoices(Choices::DISABLE),
                'placeholder' => 'placeholder.disabled',
                'required' => false,
            ])
            ->add('export');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ServiceSearch::class,
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
