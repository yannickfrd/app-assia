<?php

namespace App\Form\Evaluation;

use App\Entity\Evaluation\EvalProfPerson;
use App\Form\Utils\Choices;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EvalProfPersonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('profStatus', ChoiceType::class, [
                'choices' => Choices::getChoices(EvalProfPerson::PROF_STATUS),
                'attr' => [
                    'class' => 'js-initEval important',
                    'data-id' => 'profStatus',
                ],
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('jobCenterId')
            ->add('contractType', ChoiceType::class, [
                'choices' => Choices::getChoices(EvalProfPerson::CONTRACT_TYPE),
                'attr' => [
                    'class' => 'js-initEval',
                    'data-id' => 'contractType',
                ],
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('jobType')
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
            ->add('workPlace')
            ->add('employerName')
            ->add('transportMeansType', ChoiceType::class, [
                'choices' => Choices::getChoices(EvalProfPerson::TRANSFORT_MEANS),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('transportMeans')
            ->add('rqth', ChoiceType::class, [
                'choices' => Choices::getChoices(Choices::YES_NO_IN_PROGRESS),
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
                'label_attr' => ['class' => 'sr-only'],
                'attr' => [
                    'rows' => 5,
                    'class' => 'justify',
                    'placeholder' => 'evalProfPerson.comment',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => EvalProfPerson::class,
            'translation_domain' => 'evaluation',
        ]);
    }
}
