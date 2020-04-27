<?php

namespace App\Form\Support;

use App\Entity\GroupPeople;
use App\Form\Utils\Choices;
use App\Entity\SupportGroup;
use App\Form\Type\DateSearchType;
use App\Form\Type\ServiceSearchType;
use App\Form\Model\SupportGroupSearch;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class SupportGroupSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('fullname', null, [
                'label_attr' => ['class' => 'sr-only'],
                'attr' => [
                    'placeholder' => 'Nom et/ou prénom',
                    'class' => 'w-max-170',
                ],
            ])
            ->add('familyTypologies', ChoiceType::class, [
                'label_attr' => ['class' => 'sr-only'],
                'multiple' => true,
                'choices' => Choices::getChoices(GroupPeople::FAMILY_TYPOLOGY),
                'attr' => [
                    'class' => 'multi-select js-typology',
                ],
                'placeholder' => '-- Family Typology --',
                'required' => false,
            ])
            // ->add("nbPeople", null, [
            //     "attr" => [
            //         "class" => "w-max-100",
            //         "placeholder" => "NbPeople",
            //         "autocomplete" => "off"
            //     ]
            // ])
            ->add('status', ChoiceType::class, [
                'label_attr' => ['class' => 'sr-only'],
                'multiple' => true,
                'choices' => Choices::getChoices(SupportGroup::STATUS),
                'attr' => [
                    'class' => 'multi-select js-status',
                ],
                'placeholder' => '-- Status --',
                'required' => false,
            ])
            ->add('supportDates', ChoiceType::class, [
                'label_attr' => ['class' => 'sr-only'],
                'choices' => Choices::getChoices(SupportGroupSearch::SUPPORT_DATES),
                'placeholder' => '-- Date de suivi --',
                'required' => false,
            ])
            ->add('date', DateSearchType::class, [
                'data_class' => SupportGroupSearch::class,
            ])
            ->add('service', ServiceSearchType::class, [
                'data_class' => SupportGroupSearch::class,
            ])
            ->add('export');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SupportGroupSearch::class,
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
