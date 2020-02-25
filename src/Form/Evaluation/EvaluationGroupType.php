<?php

namespace App\Form\Evaluation;

use App\Entity\EvaluationGroup;
use App\Form\Evaluation\InitEvalGroupType;
use Symfony\Component\Form\AbstractType;
use App\Form\Evaluation\EvalBudgetGroupType;
use App\Form\Evaluation\EvalFamilyGroupType;
use App\Form\Evaluation\EvalSocialGroupType;
use App\Form\Evaluation\EvalHousingGroupType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class EvaluationGroupType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("initEvalGroup", InitEvalGroupType::class)
            ->add("evalSocialGroup", EvalSocialGroupType::class)
            ->add("evalFamilyGroup", EvalFamilyGroupType::class)
            ->add("evalBudgetGroup", EvalBudgetGroupType::class)
            ->add("evalHousingGroup", EvalHousingGroupType::class)
            ->add("evaluationPeople", CollectionType::class, [
                "entry_type"   => EvaluationPersonType::class,
                "allow_add"    => false,
                "allow_delete" => false,
                "required" => false
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            "data_class" => EvaluationGroup::class,
        ]);
    }
}
