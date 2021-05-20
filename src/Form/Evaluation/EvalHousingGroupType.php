<?php

namespace App\Form\Evaluation;

use App\Form\Utils\Choices;
use App\Entity\Organization\Service;
use App\Form\Utils\EvaluationChoices;
use Symfony\Component\Form\AbstractType;
use App\Entity\Evaluation\EvalHousingGroup;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class EvalHousingGroupType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Service */
        $service = $options['attr']['service'];

        $builder
            ->add('siaoRequest', ChoiceType::class, [
                'choices' => Choices::getChoices(EvaluationChoices::YES_NO_IN_PROGRESS_NC),
                'attr' => [
                    'data-important' => 'true',
                    'data-twin-field' => 'siaoRequest',
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
                'attr' => [
                    'data-important' => 'true',
                    'data-twin-field' => 'socialHousingRequest',
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
                'attr' => [
                    'class' => 'js-zipcode',
                ],
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
            ->add('domiciliationSearch', null, [
                'label' => ' ',
                'attr' => [
                    'class' => 'js-search',
                    'placeholder' => 'location.search.address.placeholder',
                    'autocomplete' => 'off',
                ],
                'mapped' => false,
            ])
            ->add('domiciliationAddress', null, [
                'attr' => [
                    'class' => 'js-address',
                    'readonly' => true,
                ],
            ])
            ->add('domiciliationCity', null, [
                'attr' => [
                    'class' => 'js-city',
                    'readonly' => true,
                ],
            ])
            ->add('domiciliationZipcode', null, [
                'attr' => [
                    'class' => 'js-zipcode',
                    'readonly' => true,
                ],
            ])
            ->add('housingExperience', ChoiceType::class, [
                'choices' => Choices::getChoices(EvaluationChoices::YES_NO),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('housingExpeComment')
            ->add('commentEvalHousing', null, [
                'label_attr' => ['class' => 'sr-only'],
                'attr' => [
                    'rows' => 5,
                    'class' => 'justify',
                    'placeholder' => 'evalHousingGroup.comment',
                ],
            ]);

        if (Choices::YES != $service->getPlace() && Service::SERVICE_TYPE_HOTEL != $service->getType()) {
            $builder
                ->add('housingStatus', ChoiceType::class, [
                    'choices' => Choices::getChoices(EvalHousingGroup::HOUSING_STATUS),
                    'attr' => [
                        'data-important' => 'true',
                        'data-twin-field' => 'housingStatus',
                    ],
                    'placeholder' => 'placeholder.select',
                    'required' => false,
                ])
                ->add('housingAccessType', ChoiceType::class, [
                    'choices' => Choices::getChoices(EvaluationChoices::YES_NO),
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
                    'attr' => ['class' => 'js-zipcode'],
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
                ->add('fsl')
                ->add('fslEligibility')
                ->add('cafEligibility')
                ->add('otherHelps')
                ->add('hepsPrecision');
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => EvalHousingGroup::class,
            'translation_domain' => 'evaluation',
        ]);
    }
}
