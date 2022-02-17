<?php

namespace App\Form\Evaluation;

use App\Entity\Evaluation\EvalAdmPerson;
use App\Entity\Evaluation\EvalBudgetPerson;
use App\Entity\Evaluation\EvalProfPerson;
use App\Entity\Evaluation\EvalSocialPerson;
use App\Entity\Evaluation\InitEvalPerson;
use App\Entity\Evaluation\Resource;
use App\Entity\People\Person;
use App\Form\Utils\Choices;
use App\Form\Utils\EvaluationChoices;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InitEvalPersonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('paper', ChoiceType::class, [
                'choices' => Choices::getChoices(EvaluationChoices::YES_NO_IN_PROGRESS),
                'attr' => [
                    'data-important' => 'true',
                    'data-twin-field' => 'true',
                ],
                'placeholder' => 'placeholder.select',
                'help' => 'evalAdmPerson.paper.help',
                'required' => false,
            ])
            ->add('paperType', ChoiceType::class, [
                'choices' => Choices::getChoices(EvalAdmPerson::PAPER_TYPE),
                'attr' => [
                    'data-important' => 'true',
                    'data-twin-field' => 'true',
                ],
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('rightSocialSecurity', ChoiceType::class, [
                'choices' => Choices::getChoices(EvaluationChoices::YES_NO_IN_PROGRESS),
                'attr' => [
                    'data-important' => 'true',
                    'data-twin-field' => 'true',
                ],
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('socialSecurity', ChoiceType::class, [
                'choices' => Choices::getChoices(EvalSocialPerson::SOCIAL_SECURITY),
                'attr' => [
                    'data-important' => 'true',
                    'data-twin-field' => 'true',
                ],
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('comment', null, [
                'label_attr' => ['class' => 'sr-only'],
                'attr' => [
                    'rows' => 4,
                    'class' => 'justify',
                    'placeholder' => 'initEvalPerson.comment',
                ],
            ])
        ;

        /** @var Person $person */
        $person = $options['attr']['person'];

        if ($person->getAge() < 16) {
            return;
        }

        $builder
            ->add('familyBreakdown', ChoiceType::class, [
                'choices' => Choices::getChoices(EvaluationChoices::YES_NO_PARTIAL),
                'attr' => [
                    'data-twin-field' => 'true',
                ],
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('friendshipBreakdown', ChoiceType::class, [
                'choices' => Choices::getChoices(EvaluationChoices::YES_NO_PARTIAL),
                'attr' => [
                    'data-twin-field' => 'true',
                ],
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('profStatus', ChoiceType::class, [
                'choices' => Choices::getChoices(EvalProfPerson::PROF_STATUS),
                'attr' => [
                    'data-important' => 'true',
                    'data-twin-field' => 'true',
                ],
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('contractType', ChoiceType::class, [
                'choices' => Choices::getChoices(EvalProfPerson::CONTRACT_TYPE),
                'attr' => [
                    'data-important' => 'true',
                    'data-twin-field' => 'true',
                ],
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('resource', ChoiceType::class, [
                'choices' => Choices::getChoices(EvalBudgetPerson::RESOURCES),
                'attr' => [
                    'data-twin-field' => 'true',
                    'data-important' => 'true',
                ],
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('resourceType', ChoiceType::class, [
                'choices' => Choices::getChoices(Resource::RESOURCES),
                'attr' => ['data-twin-field' => 'true'],
                'placeholder' => 'placeholder.add',
                'mapped' => false,
                'required' => false,
            ])
            ->add('resourcesAmt', MoneyType::class, [
                'attr' => [
                    'class' => 'text-right',
                    'data-amount' => 'resourcesAmt',
                    'data-important' => 'true',
                    'data-twin-field' => 'true',
                    'placeholder' => 'Amount',
                ],
            ])
            ->add('resources', CollectionType::class, [
                'entry_type' => InitResourceType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'delete_empty' => true,
                'prototype' => true,
                'by_reference' => false,
            ])
            ->add('debt', ChoiceType::class, [
                'choices' => Choices::getChoices(EvaluationChoices::YES_NO),
                'attr' => [
                    'data-important' => 'true',
                    'data-twin-field' => 'true',
                ],
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('debtsAmt', MoneyType::class, [
                'attr' => [
                    'class' => 'text-right',
                    'data-amount' => 'debtsAmt',
                    'data-twin-field' => 'true',
                    'placeholder' => 'Amount',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => InitEvalPerson::class,
            'translation_domain' => 'evaluation',
        ]);
    }
}
