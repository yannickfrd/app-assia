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
                'required' => false,
                ])
            ->add('calcul', null, ['mapped' => false])
            ->add('familyTypologies', ChoiceType::class, [
                'multiple' => true,
                'choices' => Choices::getChoices(PeopleGroup::FAMILY_TYPOLOGY),
                'attr' => [
                    'class' => 'multi-select',
                    'placeholder' => 'placeholder.familtyTypology',
                    'size' => 1,
                ],
                'required' => false,
            ])
            ->add('model', ChoiceType::class, [
                'choices' => Choices::getChoices(ExportSearch::MODELS),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('formattedSheet', CheckboxType::class, [
                'required' => false,
            ])
            ->add('anonymized', CheckboxType::class, [
                'required' => false,
            ]);
        // ->add('nbPeople', null, [
            //     'attr' => [
            //         'class' => 'w-max-100',
            //         'placeholder' => 'NbPeople',
            //         'autocomplete' => 'off',
            //     ],
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
