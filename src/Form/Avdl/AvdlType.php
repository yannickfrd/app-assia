<?php

namespace App\Form\Avdl;

use App\Entity\Avdl;
use App\Form\Utils\Choices;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class AvdlType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('diagType', ChoiceType::class, [
                'choices' => Choices::getChoices(Avdl::DIAG_TYPE),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('diagStartDate', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('diagEndDate', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('recommendationSupport', ChoiceType::class, [
                'choices' => Choices::getChoices(Avdl::RECOMMENDATION_SUPPORT),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('diagComment', TextareaType::class, [
                'attr' => [
                    'rows' => 2,
                    'placeholder' => 'avdl.diagComment.placeholder',
                ],
                'required' => false,
            ])
            ->add('supportStartDate', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('supportEndDate', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('supportType', ChoiceType::class, [
                'choices' => Choices::getChoices(Avdl::SUPPORT_TYPE),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('supportComment', TextareaType::class, [
                'attr' => [
                    'rows' => 2,
                    'placeholder' => 'avdl.supportComment.placeholder',
                ],
                'required' => false,
            ])
            ->add('endSupportReason', ChoiceType::class, [
                'choices' => Choices::getChoices(Avdl::END_SUPPORT_REASON),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('accessHousingModality', ChoiceType::class, [
                'choices' => Choices::getChoices(Avdl::ACCESS_HOUSING_MODALITY),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('propoHousingDate', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('propoResult', ChoiceType::class, [
                'choices' => Choices::getChoices(Avdl::PROPO_RESULT),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('endSupportComment', TextareaType::class, [
                'attr' => [
                    'rows' => 2,
                    'placeholder' => 'avdl.endSupportComment.placeholder',
                ],
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Avdl::class,
            'translation_domain' => 'forms',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'avdl';
    }
}
