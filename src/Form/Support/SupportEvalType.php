<?php

namespace App\Form\Support;

use App\Entity\SupportPerson;

use App\Form\Support\Evaluation\EvalAdmPersonType;
use App\Form\Support\Evaluation\EvalProfPersonType;
use App\Form\Support\Evaluation\EvalBudgetPersonType;
use App\Form\Support\Evaluation\EvalFamilyPersonType;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SupportEvalType extends AbstractType
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
            "data_class" => SupportPerson::class,
        ]);
    }
}
