<?php

namespace App\Form\Person;

use App\Entity\Person;

use App\Form\Utils\Choices;

use Symfony\Component\Form\AbstractType;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class PersonMinType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("firstname", null, [
                "label_attr" => ["class" => "sr-only"],
                "attr" => [
                    "class" => "text-capitalize",
                    "placeholder" => "Firstname",
                ]
            ])
            ->add("lastname", null, [
                "label_attr" => ["class" => "sr-only"],
                "attr" => [
                    "class" => "w-min-150 text-uppercase",
                    "placeholder" => "Lastname",
                ]
            ])
            ->add("birthdate", DateType::class, [
                "label_attr" => ["class" => "sr-only"],
                "widget" => "single_text",
                "attr" => [
                    "class" => "w-max-165",
                    "autocomplete" => "off"
                ],
            ])
            ->add("gender", ChoiceType::class, [
                "label_attr" => ["class" => "sr-only"],
                "attr" => [
                    "class" => "w-min-150"
                ],
                "choices" => Choices::getChoices(Person::GENDER),
                "placeholder" => "-- Select --",
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            "data_class" => Person::class,
            "translation_domain" => "forms"
        ]);
    }
}
