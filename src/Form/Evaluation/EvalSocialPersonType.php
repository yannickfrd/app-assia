<?php

namespace App\Form\Evaluation;

use App\Entity\Evaluation\EvalSocialPerson;
use App\Entity\People\RolePerson;
use App\Entity\Support\SupportPerson;
use App\Form\Utils\Choices;
use App\Form\Utils\EvaluationChoices;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EvalSocialPersonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var SupportPerson */
        $supportPerson = $options['attr']['supportPerson'];

        if (RolePerson::ROLE_CHILD !== $supportPerson->getRole()) {
            $this->addAdultFields($builder);
        }

        $builder
            ->add('rightSocialSecurity', ChoiceType::class, [
                'choices' => Choices::getChoices(EvaluationChoices::YES_NO_IN_PROGRESS),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('socialSecurity', ChoiceType::class, [
                'choices' => Choices::getChoices(EvalSocialPerson::SOCIAL_SECURITY),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('socialSecurityOffice')
            ->add('endRightsSocialSecurityDate', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('infoCrip', ChoiceType::class, [
                'choices' => Choices::getChoices(EvaluationChoices::YES_NO),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('infoCripDate', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
                ])
            ->add('infoCripByService', ChoiceType::class, [
                'choices' => Choices::getChoices(EvaluationChoices::YES_NO),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('infoCripComment')
            ->add('aseFollowUp', ChoiceType::class, [
                'choices' => Choices::getChoices(EvaluationChoices::YES_NO),
                'placeholder' => 'placeholder.select',
                'required' => false,
                'help' => 'evalSocialPerson.aseFollowUp.help',
            ])
            ->add('aseMeasureType', ChoiceType::class, [
                'choices' => Choices::getChoices(EvalSocialPerson::ASE_MEASURE_TYPE),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('aseComment', null, [
                'label' => false,
                'attr' => ['placeholder' => 'Ase comment'],
            ])
            ->add('healthProblem', ChoiceType::class, [
                'choices' => Choices::getChoices(EvaluationChoices::YES_NO),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('_healthProblemType', ChoiceType::class, [
                'choices' => Choices::getChoices(EvalSocialPerson::HEALTH_PROBLEMS_TYPE),
                'placeholder' => 'placeholder.add',
                'mapped' => false,
                'required' => false,
            ])
            ->add('physicalHealthProblem', HiddenType::class)
            ->add('mentalHealthProblem', HiddenType::class)
            ->add('addictionProblem', HiddenType::class)
            ->add('medicalFollowUp', ChoiceType::class, [
                'choices' => Choices::getChoices(EvaluationChoices::YES_NO_IN_PROGRESS),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('homeCareSupport', ChoiceType::class, [
                'choices' => Choices::getChoices(EvaluationChoices::YES_NO_IN_PROGRESS),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('homeCareSupportType', ChoiceType::class, [
                'choices' => Choices::getChoices(EvalSocialPerson::CARE_SUPPORT),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('wheelchair', HiddenType::class)
            ->add('reducedMobility', HiddenType::class)
            ->add('commentEvalSocialPerson', null, [
                'attr' => [
                    'rows' => 4,
                    'class' => 'justify',
                    'placeholder' => 'evalSocialPerson.comment',
                ],
            ])
            ->add('violenceVictim', ChoiceType::class, [
                'choices' => Choices::getChoices(EvaluationChoices::YES_NO),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
        ;
    }

    protected function addAdultFields(FormBuilderInterface $builder)
    {
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
            ->add('domViolenceVictim', ChoiceType::class, [
                'choices' => Choices::getChoices(EvaluationChoices::YES_NO),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => EvalSocialPerson::class,
            'translation_domain' => 'evaluation',
        ]);
    }
}
