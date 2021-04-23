<?php

namespace App\Form\Admin;

use App\Entity\Support\SupportGroup;
use App\Form\Model\Admin\ServiceIndicatorsSearch;
use App\Form\Type\DateSearchType;
use App\Form\Type\ServiceDeviceReferentSearchType;
use App\Form\Utils\Choices;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
            ->add('service', ServiceDeviceReferentSearchType::class, [
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
