<?php

namespace App\Form;

use App\Entity\Person;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;

class PersonMinType extends FormType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("lastname", null, [
                "attr" => [
                    "class" => "w-min-150 text-uppercase",
                    "placeholder" => "Lastname",
                    "required" => true
                ]
            ])
            ->add("firstname", null, [
                "attr" => [
                    "class" => "text-capitalize",
                    "placeholder" => "Firstname",
                    "required" => true
                ]
            ])
            ->add("birthdate", DateType::class, [
                "widget" => "single_text",
                "attr" => [
                    "class" => "w-max-180",
                    "placeholder" => "jj/mm/aaaa",
                    "autocomplete" => "off"
                ],
                "required" => true

            ])
            ->add("gender", ChoiceType::class, [
                "attr" => [
                    "class" => "w-min-150"
                ],
                "choices" => $this->getchoices(Person::GENDER),
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
