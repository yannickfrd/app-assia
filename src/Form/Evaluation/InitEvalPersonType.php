<?php

namespace App\Form\Evaluation;

use App\Entity\EvalAdmPerson;
use App\Entity\EvalProfPerson;
use App\Entity\EvalSocialPerson;
use App\Entity\InitEvalPerson;
use App\Form\Type\ResourcesType;
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
                    'class' => 'js-initEval important',
                    'data-id' => 'paperType',
                ],
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('rightSocialSecurity', ChoiceType::class, [
                'choices' => Choices::getChoices(Choices::YES_NO_IN_PROGRESS),
                'attr' => [
                    'class' => 'js-initEval important',
                    'data-id' => 'rightSocialSecurity',
                ],
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('socialSecurity', ChoiceType::class, [
                'choices' => Choices::getChoices(EvalSocialPerson::SOCIAL_SECURITY),
                'attr' => [
                    'class' => 'js-initEval important',
                    'data-id' => 'socialSecurity',
                ],
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('familyBreakdown', ChoiceType::class, [
                'choices' => Choices::getChoices(Choices::YES_NO_PARTIAL),
                'attr' => [
                    'class' => 'js-initEval',
                    'data-id' => 'familyBreakdown',
                ],
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('friendshipBreakdown', ChoiceType::class, [
                'choices' => Choices::getChoices(Choices::YES_NO_PARTIAL),
                'attr' => [
                    'class' => 'js-initEval',
                    'data-id' => 'friendshipBreakdown',
                ],
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('profStatus', ChoiceType::class, [
                'choices' => Choices::getChoices(EvalProfPerson::PROF_STATUS),
                'attr' => [
                    'class' => 'js-initEval important',
                    'data-id' => 'profStatus',
                ],
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('contractType', ChoiceType::class, [
                'choices' => Choices::getChoices(EvalProfPerson::CONTRACT_TYPE),
                'attr' => [
                    'class' => 'js-initEval important',
                    'data-id' => 'contractType',
                ],
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])

            ->add('resources', ResourcesType::class)

            ->add('debts', ChoiceType::class, [
                'choices' => Choices::getChoices(Choices::YES_NO),
                'attr' => [
                    'class' => 'js-initEval important',
                    'data-id' => 'debts',
                ],
                'placeholder' => 'placeholder.select',
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
