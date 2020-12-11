<?php

namespace App\Form\Export;

use App\Entity\PeopleGroup;
use App\Form\Model\ExportSearch;
use App\Form\Support\SupportSearchType;
use App\Form\Utils\Choices;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExportSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('head', CheckboxType::class, [
                'label' => 'Demandeur principal',
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
                    'data-select2-id' => 'typology',
                ],
                'placeholder' => 'placeholder.familtyTypology',
                'required' => false,
            ])
            ->add('nbPeople', null, [
                'attr' => [
                    'class' => 'w-max-100',
                    'placeholder' => 'NbPeople',
                    'autocomplete' => 'off',
                ],
            ]);
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

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ExportSearch::class,
            'csrf_protection' => false,
            'method' => 'post',
            'translation_domain' => 'forms',
        ]);
    }

    public function getParent()
    {
        return SupportSearchType::class;
    }

    public function getBlockPrefix()
    {
        return '';
    }
}
