<?php

namespace App\Form;

use App\Entity\Person;
use App\Entity\RolePerson;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;


class PersonTestType extends AbstractType
{
    public const GENDER = [
        "-- Sélectionner --" => NULL,
        "Femme" => 1,
        "Homme" => 2,
    ];

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add("lastname", TextType::class, [
            "label" => "Nom",
            "attr" => [
                "placeholder" => "Nom"
            ]
        ])
        ->add("firstname", TextType::class, [
            "label" => "Prénom",
            "attr" => [
                "placeholder" => "Prénom"
            ]
        ])
        ->add("birthdate", DateType::class, [
            "label" => "Date de naissance",
            "widget" => "single_text",
            'html5' => false,
            "format" => "dd/MM/YYYY",
            "attr" => [
                "class" => "js-datepicker input-date",
                "placeholder" => "jj/mm/aaaa",
            ],
            "required" => false
            
        ])
        ->add("gender", ChoiceType::class, [
            "label" => "Sexe",
            "choices" => $this->listGender(),
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            "data_class" => Person::class,
        ]);
    }

    public function listGender() 
    {
        return self::GENDER;
    }
}
