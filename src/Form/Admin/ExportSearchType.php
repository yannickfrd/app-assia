<?php

namespace App\Form\Admin;

use App\Entity\People\PeopleGroup;
use App\Form\Model\Admin\ExportSearch;
use App\Form\Support\Support\SupportSearchType;
use App\Form\Utils\Choices;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExportSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('head', CheckboxType::class, [
                'label' => 'head.help',
                'label_attr' => ['class' => 'custom-control-label'],
                'attr' => ['class' => 'custom-control-input checkbox'],
                'required' => false,
                ])
            ->add('calcul', null, ['mapped' => false])
            ->add('familyTypologies', ChoiceType::class, [
                'multiple' => true,
                'choices' => Choices::getChoices(PeopleGroup::FAMILY_TYPOLOGY),
                'attr' => [
                    'class' => 'multi-select',
                    'size' => 1,
                    'data-select2-id' => 'typology',
                ],
                'placeholder' => 'placeholder.familtyTypology',
                'required' => false,
            ])
            ->add('model', ChoiceType::class, [
                'choices' => Choices::getChoices(ExportSearch::MODELS),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('formattedSheet', CheckboxType::class, [
                'label_attr' => ['class' => 'custom-control-label'],
                'attr' => ['class' => 'custom-control-input checkbox'],
                'required' => false,
            ])
            ->add('anonymized', CheckboxType::class, [
                'label_attr' => ['class' => 'custom-control-label'],
                'attr' => ['class' => 'custom-control-input checkbox'],
                'required' => false,
            ]);
        // ->add('nbPeople', null, [
            //     'attr' => [
            //         'class' => 'w-max-100',
            //         'placeholder' => 'NbPeople',
            //         'autocomplete' => 'off',
            //     ],
            // ])
        // ->add('evalSocial', CheckBoxType::class, [
            //     'required' => false,
            //     'label_attr' => [
            //         'class' => 'custom-control-label',
            //     ],
            //     'attr' => ['class' => 'custom-control-input checkbox'],
            // ])
            // ->add('evalAdm', CheckBoxType::class, [
            //     'required' => false,
            //     'label_attr' => ['class' => 'custom-control-label',],
            //     'attr' => [
            //         'class' => 'custom-control-input checkbox',
            //     ],
            // ])
            // ->add('evalFamily', CheckBoxType::class, [
            //     'required' => false,
            //     'label_attr' => [
            //         'class' => 'custom-control-label',
            //     ],
            //     'attr' => ['class' => 'custom-control-input checkbox'],
            // ])
            // ->add('evalBudget', CheckBoxType::class, [
            //     'required' => false,
            //     'label_attr' => [
            //         'class' => 'custom-control-label',
            //     ],
            //     'attr' => ['class' => 'custom-control-input checkbox'],
            // ])
            // ->add('evalProf', CheckBoxType::class, [
            //     'required' => false,
            //     'label_attr' => [
            //         'class' => 'custom-control-label',
            //     ],
            //     'attr' => ['class' => 'custom-control-input checkbox'],
            // ])
            // ->add('evalHousing', CheckBoxType::class, [
            //     'required' => false,
            //     'label_attr' => [
            //         'class' => 'custom-control-label',
            //     ],
            //     'attr' => ['class' => 'custom-control-input checkbox'],
            // ])
            // ->add('evalJustice', CheckBoxType::class, [
            //     'required' => false,
            //     'label_attr' => [
            //         'class' => 'custom-control-label',
            //     ],
            //     'attr' => ['class' => 'custom-control-input checkbox'],
            // ])
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ExportSearch::class,
            'csrf_protection' => false,
            'method' => 'post',
            'translation_domain' => 'forms',
        ]);
    }

    public function getParent(): ?string
    {
        return SupportSearchType::class;
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}
