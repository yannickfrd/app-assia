<?php

namespace App\Form\Evaluation;

use App\Entity\Evaluation\EvalProfPerson;
use App\Form\Utils\Choices;
use App\Form\Utils\EvaluationChoices;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EvalProfPersonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('profStatus', ChoiceType::class, [
                'choices' => Choices::getChoices(EvalProfPerson::PROF_STATUS),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('jobCenterId')
            ->add('contractType', ChoiceType::class, [
                'choices' => Choices::getChoices(EvalProfPerson::CONTRACT_TYPE),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('workingTime', ChoiceType::class, [
                'choices' => Choices::getChoices(EvalProfPerson::WORKING_TIME),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('contractStartDate', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('contractEndDate', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('nbWorkingHours')
            ->add('workingHours')
            ->add('jobType')
            ->add('workPlace')
            ->add('employerName')
            ->add('transportMeansType', ChoiceType::class, [
                'choices' => Choices::getChoices(EvalProfPerson::TRANSFORT_MEANS),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('transportMeans')
            ->add('rqth', ChoiceType::class, [
                'choices' => Choices::getChoices(EvaluationChoices::YES_NO_IN_PROGRESS),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('endRqthDate', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('schoolLevel', ChoiceType::class, [
                'choices' => Choices::getChoices(EvalProfPerson::SCHOOL_LEVEL),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('profExperience', ChoiceType::class, [
                'choices' => Choices::getChoices(EvalProfPerson::PROF_EXPERIENCE),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('commentEvalProf', null, [
                'attr' => [
                    'rows' => 5,
                    'class' => 'justify',
                    'placeholder' => 'evalProfPerson.comment',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => EvalProfPerson::class,
            'translation_domain' => 'evaluation',
        ]);
    }
}
