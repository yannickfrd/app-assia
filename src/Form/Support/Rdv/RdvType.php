<?php

namespace App\Form\Support\Rdv;

use App\Entity\Rdv;
use App\Form\Utils\Choices;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

class RdvType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("title", null, [
                "attr" => [
                    "class" => "font-weight-bold",
                    "placeholder" => "Ajouter un titre"
                ]
            ])
            ->add("start", DateTimeType::class, [
                "widget" => "single_text",
            ])
            ->add("end", DateTimeType::class, [
                "widget" => "single_text",
            ])
            ->add("status", ChoiceType::class, [
                "choices" => Choices::getchoices(Rdv::STATUS),
                "placeholder" => "-- Select --",
                "required" => false
            ])
            ->add("location", null, [
                "attr" => [
                    "placeholder" => "Ajouter un lieu"
                ]
            ])
            ->add("content", null, [
                "attr" => [
                    // "class" => "d-none",
                    "rows" => 5,
                    "placeholder" => "Ajouter une note"
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            "data_class" => Rdv::class,
            "translation_domain" => "forms"
        ]);
    }
}
