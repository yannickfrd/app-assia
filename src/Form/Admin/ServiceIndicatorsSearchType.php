<?php

namespace App\Form\Admin;

use App\Form\Utils\Choices;
use App\Form\Type\SearchType;
use App\Form\Type\DateSearchType;
use App\Entity\Support\SupportGroup;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use App\Form\Model\Admin\ServiceIndicatorsSearch;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class ServiceIndicatorsSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('status', ChoiceType::class, [
                'multiple' => true,
                'choices' => Choices::getChoices(SupportGroup::STATUS),
                'attr' => [
                    'class' => 'multi-select',
                    'data-select2-id' => 'status',
                ],
                'placeholder' => 'placeholder.status',
                'required' => false,
            ])
            ->add('date', DateSearchType::class, [
                'data_class' => ServiceIndicatorsSearch::class,
            ])
            ->add('service', SearchType::class, [
                'data_class' => ServiceIndicatorsSearch::class,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ServiceIndicatorsSearch::class,
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
