<?php

namespace App\Form\Evaluation;

use App\Entity\SupportGroup;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class SupportGroupEvaluationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("evaluationsGroup", CollectionType::class, [
                "entry_type"   => EvaluationGroupType::class,
                "allow_add"    => false,
                "allow_delete" => false,
                "required" => false
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            "data_class" => SupportGroup::class,
            "translation_domain" => "forms"
        ]);
    }
}
