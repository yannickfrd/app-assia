<?php

namespace App\Form;

use App\Entity\RolePerson;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;


class RolePersonType extends AbstractType
{
    public const ROLE = [
        "-- Sélectionner --" => NULL,
        "Demandeur" => 1,
        "Conjoint·e" => 2,
        "Époux/se" => 3,
        "Enfant" => 4,
        "Membre de la famille" => 5,
        "Parent isolé" => 6,
        "Personne isolée" => 7,
        "Autre" => 8
    ];

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('head', CheckBoxType::class, [
                "label" => "Demandeur principal",
                "required" => false
            ])
            ->add("role", ChoiceType::class, [
                "label" => "Rôle",
                "choices" => $this->listRole(),
            ])
            ->add("person", PersonType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => RolePerson::class,
        ]);
    }

    public function listRole() 
    {
        return self::ROLE;
    }
}
