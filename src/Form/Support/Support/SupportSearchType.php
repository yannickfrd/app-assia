<?php

namespace App\Form\Support\Support;

use App\Entity\Support\SupportGroup;
use App\Form\Model\Support\SupportSearch;
use App\Form\Type\DateSearchType;
use App\Form\Type\ServiceDeviceReferentSearchType;
use App\Form\Utils\Choices;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SupportSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $dataClass = $builder->getDataClass();

        $builder
            ->add('fullname', SearchType::class, [
                'attr' => [
                    'placeholder' => 'placeholder.name_or_id',
                    'class' => 'w-min-200',
                ],
                'required' => false,
            ])
            ->add('status', ChoiceType::class, [
                'multiple' => true,
                'choices' => Choices::getChoices(SupportGroup::STATUS),
                'attr' => [
                    'class' => 'multi-select w-max-260',
                    'placeholder' => 'placeholder.status',
                    'size' => 1,
                ],
                'required' => false,
            ])
            ->add('supportDates', ChoiceType::class, [
                'choices' => Choices::getChoices(SupportSearch::SUPPORT_DATES),
                'placeholder' => '-- Date de suivi --',
                'required' => false,
            ])
            ->add('date', DateSearchType::class, [
                'data_class' => $dataClass,
            ])
            ->add('service', ServiceDeviceReferentSearchType::class, [
                'data_class' => $dataClass,
            ])
            ->add('head', CheckboxType::class, [
                'label' => 'DP',
                'required' => false,
            ])
            ->add('export')
            ->add('deleted', DeletedSearchType::class, [
                'label' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SupportSearch::class,
            'method' => 'get',
            'translation_domain' => 'forms',
            'allow_extra_fields' => true,
            'csrf_protection' => false,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}
