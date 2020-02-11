<?php

namespace App\Form\Support\Evaluation;

use App\Entity\EvaluationGroup;
use App\Form\Support\SupportEvalType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use App\Form\Support\Evaluation\EvalBudgetGroupType;
use App\Form\Support\Evaluation\EvalFamilyGroupType;
use App\Form\Support\Evaluation\EvalSocialGroupType;
use App\Form\Support\Evaluation\EvalHousingGroupType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class EvaluationGroupType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
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
            "translation_domain" => "forms"
        ]);
    }
}
