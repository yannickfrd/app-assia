<?php

namespace App\Form\Support\Rdv;

use App\Entity\Rdv;
use App\Form\Utils\Choices;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class RdvType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("title")
            ->add("startDate", DateType::class, [
                "widget" => "single_text",
            ])
            ->add("startTime", TimeType::class, [
                "widget" => "single_text",
            ])
            ->add("endTime", TimeType::class, [
                "widget" => "single_text",
            ])
            ->add("status", ChoiceType::class, [
                "choices" => Choices::getchoices(Rdv::STATUS),
                "placeholder" => "-- Select --",
                "required" => false
            ])
            ->add("location")
            ->add("content", null, [
                "attr" => [
                    "rows" => 5,
                    "placeholder" => "Write a comment about the person"
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
