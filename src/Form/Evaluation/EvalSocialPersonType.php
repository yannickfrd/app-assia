<?php

namespace App\Form\Evaluation;

use App\Entity\Evaluation\EvalSocialPerson;
use App\Entity\People\RolePerson;
use App\Entity\Support\SupportPerson;
use App\Form\Utils\Choices;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EvalSocialPersonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var SupportPerson */
        $supportPerson = $options['attr']['supportPerson'];

        if (RolePerson::ROLE_CHILD != $supportPerson->getRole()) {
            $this->addAdultFields($builder);
        }

        $builder
            ->add('infoCrip', ChoiceType::class, [
                'choices' => Choices::getChoices(Choices::YES_NO),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('infoCripDate', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
                ])
            ->add('infoCripByService', ChoiceType::class, [
                'choices' => Choices::getChoices(Choices::YES_NO),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('infoCripComment')
            ->add('aseFollowUp', ChoiceType::class, [
                'choices' => Choices::getChoices(Choices::YES_NO),
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
                'choices' => Choices::getChoices(Choices::YES_NO),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('physicalHealthProblem')
            ->add('mentalHealthProblem')
            ->add('addictionProblem')
            ->add('medicalFollowUp', ChoiceType::class, [
                'choices' => Choices::getChoices(Choices::YES_NO_IN_PROGRESS),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('homeCareSupport', ChoiceType::class, [
                'choices' => Choices::getChoices(Choices::YES_NO_IN_PROGRESS),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('homeCareSupportType', ChoiceType::class, [
                'choices' => Choices::getChoices(EvalSocialPerson::CARE_SUPPORT),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('wheelchair')
            ->add('reducedMobility')
            ->add('commentEvalSocialPerson', null, [
                'label_attr' => ['class' => 'sr-only'],
                'attr' => [
                    'rows' => 4,
                    'class' => 'justify',
                    'placeholder' => 'evalSocialPerson.comment',
                ],
            ]);
    }

    protected function addAdultFields(FormBuilderInterface $builder)
    {
        $builder
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
            ->add('socialSecurityOffice')
            ->add('endRightsSocialSecurityDate', DateType::class, [
                'widget' => 'single_text',
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
            ->add('violenceVictim', ChoiceType::class, [
                'choices' => Choices::getChoices(Choices::YES_NO),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('domViolenceVictim', ChoiceType::class, [
                'choices' => Choices::getChoices(Choices::YES_NO),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => EvalSocialPerson::class,
            'translation_domain' => 'evaluation',
        ]);
    }
}
