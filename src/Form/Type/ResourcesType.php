<?php

namespace App\Form\Type;

use App\Form\Utils\Choices;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class ResourcesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
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
                'class' => 'js-money js-resourcesAmt js-initEval text-right',
                'data-id' => 'resourcesAmt',
            ],
            'required' => false,
        ])
        ->add('disAdultAllowance', IntegerType::class, [
            'attr' => [
                'class' => 'js-initEval',
                'data-id' => 'disAdultAllowance',
            ],
        ])
        ->add('disChildAllowance', IntegerType::class, [
            'attr' => [
                'class' => 'js-initEval',
                'data-id' => 'disChildAllowance',
            ],
        ])
        ->add('unemplBenefit', IntegerType::class, [
            'attr' => [
                'class' => 'js-initEval',
                'data-id' => 'unemplBenefit',
            ],
        ])
        ->add('asylumAllowance', IntegerType::class, [
            'attr' => [
                'class' => 'js-initEval',
                'data-id' => 'asylumAllowance',
            ],
        ])
        ->add('tempWaitingAllowance', IntegerType::class, [
            'attr' => [
                'class' => 'js-initEval',
                'data-id' => 'tempWaitingAllowance',
            ],
        ])
        ->add('familyAllowance', IntegerType::class, [
            'attr' => [
                'class' => 'js-initEval',
                'data-id' => 'familyAllowance',
            ],
        ])
        ->add('solidarityAllowance', IntegerType::class, [
            'attr' => [
                'class' => 'js-initEval',
                'data-id' => 'solidarityAllowance',
            ],
        ])
        ->add('paidTraining', IntegerType::class, [
            'attr' => [
                'class' => 'js-initEval',
                'data-id' => 'paidTraining',
            ],
        ])
        ->add('youthGuarantee', IntegerType::class, [
            'attr' => [
                'class' => 'js-initEval',
                'data-id' => 'youthGuarantee',
            ],
        ])
        ->add('maintenance', IntegerType::class, [
            'attr' => [
                'class' => 'js-initEval',
                'data-id' => 'maintenance',
            ],
        ])
        ->add('activityBonus', IntegerType::class, [
            'attr' => [
                'class' => 'js-initEval',
                'data-id' => 'activityBonus',
            ],
        ])
        ->add('pensionBenefit', IntegerType::class, [
            'attr' => [
                'class' => 'js-initEval',
                'data-id' => 'pensionBenefit',
            ],
        ])
        ->add('minimumIncome', IntegerType::class, [
            'attr' => [
                'class' => 'js-initEval',
                'data-id' => 'minimumIncome',
            ],
        ])
        ->add('salary', IntegerType::class, [
            'attr' => [
                'class' => 'js-initEval',
                'data-id' => 'salary',
            ],
        ])
        ->add('ressourceOther', IntegerType::class, [
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
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'inherit_data' => true,
        ]);
    }

    public function getBlockPrefix()
    {
        return '';
    }
}
