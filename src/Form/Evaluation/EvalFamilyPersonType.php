<?php

namespace App\Form\Evaluation;

use App\Entity\Evaluation\EvalFamilyPerson;
use App\Entity\People\RolePerson;
use App\Entity\Support\SupportPerson;
use App\Form\Type\LocationType;
use App\Form\Utils\Choices;
use App\Form\Utils\EvaluationChoices;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EvalFamilyPersonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var SupportPerson */
        $supportPerson = $options['attr']['supportPerson'];

        if (RolePerson::ROLE_CHILD === $supportPerson->getRole()) {
            $this->addChildFields($builder);
        } else {
            $this->addAdultFields($builder);
        }

        $builder
            ->add('pmiFollowUp', ChoiceType::class, [
                'choices' => Choices::getChoices(EvaluationChoices::YES_NO),
                'placeholder' => 'placeholder.select',
                'required' => false,
                // 'help' => 'pmiFollowUp.help',
            ])
            ->add('pmiName')
            ->add('commentEvalFamilyPerson', null, [
                'attr' => [
                    'rows' => 4,
                    'class' => 'justify',
                    'placeholder' => 'evalFamilyPerson.comment',
                ],
            ])
            ->add('unbornChild', ChoiceType::class, [
                'choices' => Choices::getChoices(EvaluationChoices::YES_NO),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('expDateChildbirth', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('pregnancyType', ChoiceType::class, [
                'choices' => Choices::getChoices(EvalFamilyPerson::PREGNANCY_TYPE),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
        ;
    }

    protected function addAdultFields(FormBuilderInterface $builder)
    {
        $builder
            ->add('maritalStatus', ChoiceType::class, [
                'choices' => Choices::getChoices(EvalFamilyPerson::MARITAL_STATUS),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('noConciliationOrder', ChoiceType::class, [
                'choices' => Choices::getChoices(EvaluationChoices::YES_NO),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('protectiveMeasure', ChoiceType::class, [
                'choices' => Choices::getChoices(EvaluationChoices::YES_NO_IN_PROGRESS),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('protectiveMeasureType', ChoiceType::class, [
                'choices' => Choices::getChoices(EvalFamilyPerson::PROTECTIVE_MEASURE_TYPE),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ]);
    }

    protected function addChildFields(FormBuilderInterface $builder): void
    {
        $builder
            ->add('schoolOrChildcare', ChoiceType::class, [
                'choices' => Choices::getChoices(EvaluationChoices::YES_NO),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('schoolChildcareType', ChoiceType::class, [
                'choices' => Choices::getChoices(EvalFamilyPerson::CHILDCARE_SCHOOL),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('schoolLocation', LocationType::class, [
                'data_class' => EvalFamilyPerson::class,
                'attr' => [
                    'fullAddress' => 'schoolFullAddress',
                    'city' => 'schoolCity',
                    'address' => false,
                    'zipcode' => 'schoolZipcode',
                    'comment' => 'schoolComment',
                    'location_comment_label' => 'school.comment',
                    'location_comment_help' => 'school.comment.help',
                    'location_search_placeholder' => 'location.city.placeholder',
                ],
            ])
            ->add('childToHost', ChoiceType::class, [
                'choices' => Choices::getChoices(EvalFamilyPerson::CHILD_TO_HOST),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('childDependance', ChoiceType::class, [
                'choices' => Choices::getChoices(EvalFamilyPerson::CHILD_DEPENDANCE),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => EvalFamilyPerson::class,
            'translation_domain' => 'evaluation',
        ]);
    }
}
