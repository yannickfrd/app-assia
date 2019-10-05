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


class PersonType extends AbstractType
{
    public const GENDER = [
        "-- Sélectionner --" => NULL,
        "Femme" => 1,
        "Homme" => 2,
    ];

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        // ->add("id")
        ->add("lastname", NULL, [
            "label" => "Nom",
            "attr" => [
                "placeholder" => "Nom"
            ]
        ])
        ->add("firstname", NULL, [
            "label" => "Prénom",
            "attr" => [
                "placeholder" => "Prénom"
            ]
        ])
        ->add("birthdate", DateType::class, [
            "label" => "Date de naissance",
            "widget" => "single_text",
            "attr" => [
                "class" => "col-md-12"
            ],
            "required" => false
            
        ])
        // ->add("age")
        ->add("gender", ChoiceType::class, [
            "label" => "Sexe",
            "attr" => [
                "class" => "col-md-12"
            ],
            // "placeholder" => "Sélectionner une option",
            "choices" => $this->listGender(),
        ])
        ->add("comment",NULL, [
            "label" => "Commentaire",
            "attr" => [
                "rows" => 5,
                "placeholder" => "Saisir un commentaire sur la personne"
            ]
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
