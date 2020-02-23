<?php

namespace App\Form\Support;

use App\Entity\SupportPerson;
use App\Form\InitEvalPersonType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SupportPersonInitEvalType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("initEvalPerson", InitEvalPersonType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            "data_class" => SupportPerson::class,
            "translation_domain" => "forms",
        ]);
    }
}
