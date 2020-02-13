<?php

namespace App\Form\Support;

use App\Entity\SupportPerson;
use App\Form\Utils\Choices;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class SupportPersonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("startDate", DateType::class, [
                "label_attr" => ["class" => "sr-only"],
                "widget" => "single_text",
                "attr" => [
                    "class" => "w-max-165",
                ],
                "required" => true
            ])
            ->add("endDate", DateType::class, [
                "label_attr" => ["class" => "sr-only"],
                "widget" => "single_text",
                "attr" => [
                    "class" => "w-max-165",
                ],
                "required" => false
            ])
            ->add("status", ChoiceType::class, [
                "label_attr" => ["class" => "sr-only"],
                "choices" => Choices::getChoices(SupportPerson::STATUS),
                "attr" => [
                    "class" => "w-min-150"
                ],
                "placeholder" => "-- Select --",
                "required" => true
            ])
            ->add("comment", null, [
                "label_attr" => ["class" => "sr-only"],
                "attr" => [
                    "rows" => 1,
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            "data_class" => SupportPerson::class,
            "translation_domain" => "forms",
        ]);
    }
}
