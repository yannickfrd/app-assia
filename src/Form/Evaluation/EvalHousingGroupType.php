<?php

namespace App\Form\Evaluation;

use App\Entity\EvalHousingGroup;
use App\Form\Utils\Choices;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EvalHousingGroupType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('housingStatus', ChoiceType::class, [
                'choices' => Choices::getChoices(EvalHousingGroup::HOUSING_STATUS),
                'attr' => [
                    'class' => 'js-initEval',
                    'data-id' => 'housingStatus',
                ],
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('siaoRequest', ChoiceType::class, [
                'choices' => Choices::getChoices(Choices::YES_NO_IN_PROGRESS_NC),
                'attr' => [
                    'class' => 'js-initEval',
                    'data-id' => 'siaoRequest',
                ],
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('siaoRequestDate', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('siaoUpdatedRequestDate', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('socialHousingRequest', ChoiceType::class, [
                'choices' => Choices::getChoices(Choices::YES_NO_IN_PROGRESS_NC),
                'attr' => [
                    'class' => 'js-initEval',
                    'data-id' => 'socialHousingRequest',
                ],
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('socialHousingRequestId')
            ->add('socialHousingRequestDate', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('socialHousingUpdatedRequestDate', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('housingWishes', null, [
                'help' => 'T1, T2, T3, T4, T5...',
            ])
            ->add('citiesWishes')
            ->add('specificities')
            ->add('syplo', ChoiceType::class, [
                'choices' => Choices::getChoices(Choices::YES_NO_IN_PROGRESS),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('syploId')
            ->add('syploDate', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('daloCommission', ChoiceType::class, [
                'choices' => Choices::getChoices(Choices::YES_NO_IN_PROGRESS),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('daloId')
            ->add('daloRecordDate', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('daloRequalifiedDaho', ChoiceType::class, [
                'choices' => Choices::getChoices(Choices::YES_NO),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('daloDecisionDate', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('daloTribunalAction', ChoiceType::class, [
                'choices' => Choices::getChoices(Choices::YES_NO_IN_PROGRESS),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('daloTribunalActionDate', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('collectiveAgreementHousing', ChoiceType::class, [
                'choices' => Choices::getChoices(Choices::YES_NO),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('collectiveAgreementHousingDate', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('hsgActionEligibility', ChoiceType::class, [
                'choices' => Choices::getChoices(Choices::YES_NO),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('hsgActionRecord', ChoiceType::class, [
                'choices' => Choices::getChoices(Choices::YES_NO),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('hsgActionDate', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('hsgActionDept', null, [
                'attr' => [
                    'class' => 'js-zipcode',
                ],
                'help' => 'Department or zip code',
            ])
            ->add('hsgActionRecordId')
            ->add('expulsionInProgress', ChoiceType::class, [
                'choices' => Choices::getChoices(Choices::YES_NO),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('publicForce', ChoiceType::class, [
                'choices' => Choices::getChoices(Choices::YES_NO),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('publicForceDate', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('expulsionComment')
            ->add('housingExperience', ChoiceType::class, [
                'choices' => Choices::getChoices(Choices::YES_NO),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('housingExpeComment')
            ->add('fsl')
            ->add('fslEligibility')
            ->add('cafEligibility')
            ->add('otherHelps')
            ->add('hepsPrecision')
            ->add('domiciliation', ChoiceType::class, [
                'choices' => Choices::getChoices(Choices::YES_NO_IN_PROGRESS),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('startDomiciliationDate', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('endDomiciliationDate', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('domiciliationAddress')
            ->add('domiciliationCity')
            ->add('domiciliationDept', null, [
                'attr' => [
                    'class' => 'js-zipcode',
                ],
                'help' => 'Department or zip code',
            ])
            ->add('housingAccessType', ChoiceType::class, [
                'choices' => Choices::getChoices(Choices::YES_NO),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('housingArrivalDate', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('housingAddress')
            ->add('housingCity')
            ->add('housingDept', null, [
                'attr' => [
                    'class' => 'js-zipcode',
                ],
                'help' => 'Department or zip code',
            ])
            ->add('commentEvalHousing', null, [
                'label_attr' => ['class' => 'sr-only'],
                'attr' => [
                    'rows' => 5,
                    'placeholder' => 'Write a comment about the housing situation',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => EvalHousingGroup::class,
            'translation_domain' => 'evaluation',
        ]);
    }
}
