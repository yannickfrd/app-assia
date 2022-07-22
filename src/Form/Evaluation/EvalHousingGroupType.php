<?php

namespace App\Form\Evaluation;

use App\Entity\Evaluation\EvalHousingGroup;
use App\Entity\Organization\Service;
use App\Form\Utils\Choices;
use App\Form\Utils\EvaluationChoices;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EvalHousingGroupType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Service */
        $service = $options['attr']['service'];

        $builder
            ->add('siaoRequest', ChoiceType::class, [
                'choices' => Choices::getChoices(EvaluationChoices::YES_NO_IN_PROGRESS_NC),
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
            ->add('siaoRequestDept', ChoiceType::class, [
                'choices' => Choices::getChoices(EvaluationChoices::DEPARTMENTS),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('siaoRecommendation', ChoiceType::class, [
                'choices' => Choices::getChoices(EvalHousingGroup::SIAO_RECOMMENDATION),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('socialHousingRequest', ChoiceType::class, [
                'choices' => Choices::getChoices(EvaluationChoices::YES_NO_IN_PROGRESS_NC),
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
                'help' => 'evalHousingGroup.housingWishes.help',
            ])
            ->add('citiesWishes')
            ->add('specificities')
            ->add('syplo', ChoiceType::class, [
                'choices' => Choices::getChoices(EvaluationChoices::YES_NO_IN_PROGRESS),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('syploId')
            ->add('syploDate', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('daloAction', ChoiceType::class, [
                'choices' => Choices::getChoices(EvaluationChoices::YES_NO_IN_PROGRESS),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('daloType', ChoiceType::class, [
                'choices' => Choices::getChoices(EvalHousingGroup::DALO_TYPE),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('daloId')
            ->add('daloRecordDate', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('daloDecisionDate', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('daloTribunalAction', ChoiceType::class, [
                'choices' => Choices::getChoices(EvaluationChoices::YES_NO_IN_PROGRESS),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('daloTribunalActionDate', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
            ])
            // ->add('collectiveAgreementHousing', ChoiceType::class, [
            //     'choices' => Choices::getChoices(EvaluationChoices::YES_NO),
            //     'placeholder' => 'placeholder.select',
            //     'required' => false,
            // ])
            // ->add('collectiveAgreementHousingDate', DateType::class, [
            //     'widget' => 'single_text',
            //     'required' => false,
            // ])
            ->add('hsgActionEligibility', ChoiceType::class, [
                'choices' => Choices::getChoices(EvaluationChoices::YES_NO),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('hsgActionRecord', ChoiceType::class, [
                'choices' => Choices::getChoices(EvaluationChoices::YES_NO),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('hsgActionDate', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('hsgActionDept', null, [
                'help' => 'location.department.help',
            ])
            ->add('hsgActionRecordId')
            ->add('domiciliation', ChoiceType::class, [
                'choices' => Choices::getChoices(EvaluationChoices::YES_NO_IN_PROGRESS),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('domiciliationType', ChoiceType::class, [
                'choices' => Choices::getChoices(EvalHousingGroup::DOMICILIATION_TYPE),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('domiciliationComment')
            ->add('startDomiciliationDate', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('endDomiciliationDate', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('_domiciliationSearch', null, [
                'label' => ' ',
                'attr' => [
                    'placeholder' => 'location.search.address.placeholder',
                    'autocomplete' => 'off',
                ],
                'mapped' => false,
            ])
            ->add('domiciliationAddress', null, [
                'attr' => ['readonly' => true],
            ])
            ->add('domiciliationCity', null, [
                'attr' => ['readonly' => true],
            ])
            ->add('domiciliationZipcode', null, [
                'attr' => ['readonly' => true],
            ])
            ->add('housingExperience', ChoiceType::class, [
                'choices' => Choices::getChoices(EvaluationChoices::YES_NO),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('housingExpeComment')
            ->add('commentEvalHousing', null, [
                'attr' => [
                    'rows' => 5,
                    'class' => 'justify',
                    'placeholder' => 'evalHousingGroup.comment',
                ],
            ]);

        if (Choices::YES !== $service->getPlace() && Service::SERVICE_TYPE_HOTEL !== $service->getType()) {
            $builder
                ->add('housingStatus', ChoiceType::class, [
                    'choices' => Choices::getChoices(EvalHousingGroup::HOUSING_STATUS),
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
                    'help' => 'location.department.help',
                ])
                ->add('expulsionInProgress', ChoiceType::class, [
                    'choices' => Choices::getChoices(EvaluationChoices::YES_NO),
                    'placeholder' => 'placeholder.select',
                    'required' => false,
                ])
                ->add('publicForce', ChoiceType::class, [
                    'choices' => Choices::getChoices(EvaluationChoices::YES_NO),
                    'placeholder' => 'placeholder.select',
                    'required' => false,
                ])
                ->add('publicForceDate', DateType::class, [
                    'widget' => 'single_text',
                    'required' => false,
                ])
                ->add('expulsionComment')
                ->add('_hsgHelps', ChoiceType::class, [
                    'choices' => Choices::getChoices(EvalHousingGroup::HOUSING_HELPS),
                    'placeholder' => 'placeholder.add',
                    'mapped' => false,
                    'required' => false,
                ])
                ->add('fsl', HiddenType::class)
                ->add('fslEligibility', HiddenType::class)
                ->add('cafEligibility', HiddenType::class)
                ->add('otherHelps', HiddenType::class)
                ->add('hepsPrecision', HiddenType::class);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => EvalHousingGroup::class,
            'translation_domain' => 'evaluation',
        ]);
    }
}
