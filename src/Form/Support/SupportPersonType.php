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
                "widget" => "single_text",
                "attr" => [
                    "class" => "w-max-180",
                ],
                "required" => true
            ])
            ->add("endDate", DateType::class, [
                "widget" => "single_text",
                "attr" => [
                    "class" => "w-max-180",
                ],
                "required" => false
            ])
            ->add("status", ChoiceType::class, [
                "choices" => Choices::getChoices(SupportPerson::STATUS),
                "placeholder" => "-- Select --",
                "required" => true
            ])
            ->add("comment", null, [
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
