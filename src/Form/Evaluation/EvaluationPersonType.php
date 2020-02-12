<?php

namespace App\Form\Evaluation;

use App\Entity\EvaluationPerson;
use App\Form\Evaluation\EvalAdmPersonType;
use App\Form\Evaluation\EvalProfPersonType;
use App\Form\Evaluation\EvalBudgetPersonType;
use App\Form\Evaluation\EvalFamilyPersonType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EvaluationPersonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("evalAdmPerson", EvalAdmPersonType::class)
            ->add("evalFamilyPerson", EvalFamilyPersonType::class)
            ->add("evalProfPerson", EvalProfPersonType::class)
            ->add("evalBudgetPerson", EvalBudgetPersonType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            "data_class" => EvaluationPerson::class,
        ]);
    }
}
