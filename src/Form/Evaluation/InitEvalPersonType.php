<?php

namespace App\Form\Evaluation;

use App\Entity\EvalAdmPerson;
use App\Entity\EvalProfPerson;
use App\Entity\EvalSocialPerson;
use App\Entity\InitEvalPerson;
use App\Form\Utils\Choices;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InitEvalPersonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('paperType', ChoiceType::class, [
                'choices' => Choices::getChoices(EvalAdmPerson::PAPER_TYPE),
                'attr' => [
                    'class' => 'js-initEval',
                    'data-id' => 'paperType',
                ],
                'placeholder' => '-- Select --',
                'required' => false,
            ])
            ->add('rightSocialSecurity', ChoiceType::class, [
                'choices' => Choices::getChoices(Choices::YES_NO_IN_PROGRESS),
                'attr' => [
                    'class' => 'js-initEval',
                    'data-id' => 'rightSocialSecurity',
                ],
                'placeholder' => '-- Select --',
                'required' => false,
            ])
            ->add('socialSecurity', ChoiceType::class, [
                'choices' => Choices::getChoices(EvalSocialPerson::SOCIAL_SECURITY),
                'attr' => [
                    'class' => 'js-initEval',
                    'data-id' => 'socialSecurity',
                ],
                'placeholder' => '-- Select --',
                'required' => false,
            ])
            ->add('familyBreakdown', ChoiceType::class, [
                'choices' => Choices::getChoices(Choices::YES_NO_PARTIAL),
                'attr' => [
                    'class' => 'js-initEval',
                    'data-id' => 'familyBreakdown',
                ],
                'placeholder' => '-- Select --',
                'required' => false,
            ])
            ->add('friendshipBreakdown', ChoiceType::class, [
                'choices' => Choices::getChoices(Choices::YES_NO_PARTIAL),
                'attr' => [
                    'class' => 'js-initEval',
                    'data-id' => 'friendshipBreakdown',
                ],
                'placeholder' => '-- Select --',
                'required' => false,
            ])
            ->add('profStatus', ChoiceType::class, [
                'choices' => Choices::getChoices(EvalProfPerson::PROF_STATUS),
                'attr' => [
                    'class' => 'js-initEval',
                    'data-id' => 'profStatus',
                ],
                'placeholder' => '-- Select --',
                'required' => false,
            ])
            ->add('contractType', ChoiceType::class, [
                'choices' => Choices::getChoices(EvalProfPerson::CONTRACT_TYPE),
                'attr' => [
                    'class' => 'js-initEval',
                    'data-id' => 'contractType',
                ],
                'placeholder' => '-- Select --',
                'required' => false,
            ])
            ->add('resources', ChoiceType::class, [
                'choices' => Choices::getChoices(Choices::YES_NO_IN_PROGRESS),
                'attr' => [
                    'class' => 'js-initEval',
                    'data-id' => 'resources',
                ],
                'placeholder' => '-- Select --',
                'required' => false,
            ])
            ->add('resourcesAmt', MoneyType::class, [
                'attr' => [
                    'class' => 'js-resourcesAmt js-initEval text-right',
                    'data-id' => 'resourcesAmt',
                ],
                'required' => false,
            ])
            ->add('disAdultAllowance', null, [
                'attr' => [
                    'class' => 'js-initEval',
                    'data-id' => 'disAdultAllowance',
                ],
            ])
            ->add('disChildAllowance', null, [
                'attr' => [
                    'class' => 'js-initEval',
                    'data-id' => 'disChildAllowance',
                ],
            ])
            ->add('unemplBenefit', null, [
                'attr' => [
                    'class' => 'js-initEval',
                    'data-id' => 'unemplBenefit',
                ],
            ])
            ->add('asylumAllowance', null, [
                'attr' => [
                    'class' => 'js-initEval',
                    'data-id' => 'asylumAllowance',
                ],
            ])
            ->add('tempWaitingAllowance', null, [
                'attr' => [
                    'class' => 'js-initEval',
                    'data-id' => 'tempWaitingAllowance',
                ],
            ])
            ->add('familyAllowance', null, [
                'attr' => [
                    'class' => 'js-initEval',
                    'data-id' => 'familyAllowance',
                ],
            ])
            ->add('solidarityAllowance', null, [
                'attr' => [
                    'class' => 'js-initEval',
                    'data-id' => 'solidarityAllowance',
                ],
            ])
            ->add('paidTraining', null, [
                'attr' => [
                    'class' => 'js-initEval',
                    'data-id' => 'paidTraining',
                ],
            ])
            ->add('youthGuarantee', null, [
                'attr' => [
                    'class' => 'js-initEval',
                    'data-id' => 'youthGuarantee',
                ],
            ])
            ->add('maintenance', null, [
                'attr' => [
                    'class' => 'js-initEval',
                    'data-id' => 'maintenance',
                ],
            ])
            ->add('activityBonus', null, [
                'attr' => [
                    'class' => 'js-initEval',
                    'data-id' => 'activityBonus',
                ],
            ])
            ->add('pensionBenefit', null, [
                'attr' => [
                    'class' => 'js-initEval',
                    'data-id' => 'pensionBenefit',
                ],
            ])
            ->add('minimumIncome', null, [
                'attr' => [
                    'class' => 'js-initEval',
                    'data-id' => 'minimumIncome',
                ],
            ])
            ->add('salary', null, [
                'attr' => [
                    'class' => 'js-initEval',
                    'data-id' => 'salary',
                ],
            ])
            ->add('ressourceOther', null, [
                'label_attr' => [
                    'class' => 'js-initEval js-noText',
                    'data-id' => 'ressourceOther',
                ],
            ])
            ->add('ressourceOtherPrecision', null, [
                'attr' => [
                    'class' => 'js-initEval',
                    'data-id' => 'ressourceOtherPrecision',
                    'placeholder' => 'Other ressource(s)...',
                ],
            ])
            ->add('disAdultAllowanceAmt', MoneyType::class, [
                'attr' => [
                    'class' => 'js-money js-resources js-initEval text-right',
                    'data-id' => 'disAdultAllowanceAmt',
                ],
                'required' => false,
            ])
            ->add('disChildAllowanceAmt', MoneyType::class, [
                'attr' => [
                    'class' => 'js-money js-resources js-initEval text-right',
                    'data-id' => 'disChildAllowanceAmt',
                ],
                'required' => false,
            ])
            ->add('unemplBenefitAmt', MoneyType::class, [
                'attr' => [
                    'class' => 'js-money js-resources js-initEval text-right',
                    'data-id' => 'unemplBenefitAmt',
                ],
                'required' => false,
            ])
            ->add('asylumAllowanceAmt', MoneyType::class, [
                'attr' => [
                    'class' => 'js-money js-resources js-initEval text-right',
                    'data-id' => 'asylumAllowanceAmt',
                ],
                'required' => false,
            ])
            ->add('tempWaitingAllowanceAmt', MoneyType::class, [
                'attr' => [
                    'class' => 'js-money js-resources js-initEval text-right',
                    'data-id' => 'tempWaitingAllowanceAmt',
                ],
                'required' => false,
            ])
            ->add('familyAllowanceAmt', MoneyType::class, [
                'attr' => [
                    'class' => 'js-money js-resources js-initEval text-right',
                    'data-id' => 'familyAllowanceAmt',
                ],
                'required' => false,
            ])
            ->add('solidarityAllowanceAmt', MoneyType::class, [
                'attr' => [
                    'class' => 'js-money js-resources js-initEval text-right',
                    'data-id' => 'solidarityAllowanceAmt',
                ],
                'required' => false,
            ])
            ->add('paidTrainingAmt', MoneyType::class, [
                'attr' => [
                    'class' => 'js-money js-resources js-initEval text-right',
                    'data-id' => 'paidTrainingAmt',
                ],
                'required' => false,
            ])
            ->add('youthGuaranteeAmt', MoneyType::class, [
                'attr' => [
                    'class' => 'js-money js-resources js-initEval text-right',
                    'data-id' => 'youthGuaranteeAmt',
                ],
                'required' => false,
            ])
            ->add('maintenanceAmt', MoneyType::class, [
                'attr' => [
                    'class' => 'js-money js-resources js-initEval text-right',
                    'data-id' => 'maintenanceAmt',
                ],
                'required' => false,
            ])
            ->add('activityBonusAmt', MoneyType::class, [
                'attr' => [
                    'class' => 'js-money js-resources js-initEval text-right',
                    'data-id' => 'activityBonusAmt',
                ],
                'required' => false,
            ])
            ->add('pensionBenefitAmt', MoneyType::class, [
                'attr' => [
                    'class' => 'js-money js-resources js-initEval text-right',
                    'data-id' => 'pensionBenefitAmt',
                ],
                'required' => false,
            ])
            ->add('minimumIncomeAmt', MoneyType::class, [
                'attr' => [
                    'class' => 'js-money js-resources js-initEval text-right',
                    'data-id' => 'minimumIncomeAmt',
                ],
                'required' => false,
            ])
            ->add('salaryAmt', MoneyType::class, [
                'attr' => [
                    'class' => 'js-money js-resources js-initEval text-right',
                    'data-id' => 'salaryAmt',
                ],
                'required' => false,
            ])
            ->add('ressourceOtherAmt', MoneyType::class, [
                'attr' => [
                    'class' => 'js-money js-resources js-initEval text-right',
                    'data-id' => 'ressourceOtherAmt',
                ],
                'required' => false,
            ])
            ->add('debts', ChoiceType::class, [
                'choices' => Choices::getChoices(Choices::YES_NO),
                'attr' => [
                    'class' => 'js-initEval',
                    'data-id' => 'debts',
                ],
                'placeholder' => '-- Select --',
                'required' => false,
            ])
            ->add('debtsAmt', MoneyType::class, [
                'attr' => [
                    'class' => 'js-money js-debtsAmt js-initEval text-right',
                    'data-id' => 'debtsAmt',
                ],
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => InitEvalPerson::class,
            'translation_domain' => 'evaluation',
        ]);
    }
}
