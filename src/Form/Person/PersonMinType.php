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
            ->add("lastname", null, [
                "label_attr" => ["class" => "sr-only"],
                "attr" => [
                    "class" => "w-min-150 text-uppercase",
                    "placeholder" => "Lastname",
                    "required" => true
                ]
            ])
            ->add("firstname", null, [
                "label_attr" => ["class" => "sr-only"],
                "attr" => [
                    "class" => "text-capitalize",
                    "placeholder" => "Firstname",
                    "required" => true
                ]
            ])
            ->add("birthdate", DateType::class, [
                "label_attr" => ["class" => "sr-only"],
                "widget" => "single_text",
                "attr" => [
                    "class" => "w-max-165",
                    "placeholder" => "jj/mm/aaaa",
                    "autocomplete" => "off"
                ],
                "required" => true
            ])
            ->add("gender", ChoiceType::class, [
                "label_attr" => ["class" => "sr-only"],
                "attr" => [
                    "class" => "w-min-150"
                ],
                "choices" => Choices::getChoices(Person::GENDER),
                "placeholder" => "-- Select --",
                "required" => true
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
