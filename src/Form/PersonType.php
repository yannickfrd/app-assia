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
                "class" => "col-md-6"
            ],
            "required" => false
            
        ])
        ->add("gender", ChoiceType::class, [
            "label" => "Sexe",
            "attr" => [
                "class" => "col-md-6"
            ],
            // "placeholder" => "Sélectionner une option",
            "choices" => $this->listGender(),
        ])
        ->add('rolesPerson', CollectionType::class, [
            
            'entry_type'   => RolePersonType::class,
            'allow_add'    => true,
            'allow_delete' => false
        ])
        // ->add("rolesPerson", EntityType::class, [
        //     "class" => RolePerson::class,
        //     "choice_label" => "role",
        //     "label" => "Rôle",
        //     "attr" => [
        //         "class" => "col-md-6"
        //     ],
        // ])
        // ->add("rolesPerson", ChoiceType::class, [
        //     "label" => "Rôle",
        //     "attr" => [
        //         "class" => "col-md-6"
        //     ],
        //     "choices" => [
        //         "-- Sélectionner --" => NULL,
        //         "DP" => 1,
        //         "Conjoint(e)" => 2,
        //         "Enfant" => 3,
        //         "Autre" => 4
        //     ],
        // ])
        ->add("comment",NULL, [
            "label" => "Commentaire",
            "attr" => [
                "rows" => 5,
                "placeholder" => "Saisir un commentaire sur la personne"
            ]
        ])
        // ->add("creationDate", DateTimeType::class, [
        //     "widget" => "single_text",
        //     "format" => "dd/MM/YYY H:m",
        // ])                    
        ;
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
