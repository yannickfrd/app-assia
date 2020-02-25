<?php

namespace App\Form\Evaluation;

use App\Entity\EvaluationPerson;
use App\Form\Evaluation\InitEvalPersonType;
use App\Form\Evaluation\EvalJusticePersonType;
use Symfony\Component\Form\AbstractType;
use App\Form\Evaluation\EvalAdmPersonType;
use App\Form\Evaluation\EvalProfPersonType;
use App\Form\Evaluation\EvalBudgetPersonType;
use App\Form\Evaluation\EvalFamilyPersonType;
use App\Form\Evaluation\EvalSocialPersonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EvaluationPersonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("initEvalPerson", InitEvalPersonType::class)
            ->add("evalAdmPerson", EvalAdmPersonType::class)
            ->add("evalBudgetPerson", EvalBudgetPersonType::class)
            ->add("evalFamilyPerson", EvalFamilyPersonType::class)
            ->add("evalJusticePerson", EvalJusticePersonType::class)
            ->add("evalProfPerson", EvalProfPersonType::class)
            ->add("evalSocialPerson", EvalSocialPersonType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            "data_class" => EvaluationPerson::class,
            // "translation_domain" => "forms"
        ]);
    }
}
