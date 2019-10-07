<?php

namespace App\Form;

use App\Entity\GroupPeople;
use App\Entity\RolePerson;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class GroupPeopleType extends FormType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add("familyTypology", ChoiceType::class, [
            "attr" => [
                "class" => "col-md-12"
            ],
            "choices" => $this->getchoices(GroupPeople::FAMILY_TYPOLOGY)
            ])
        ->add("nbPeople", NULL, [
            "attr" => [
                "class" => "col-md-4"
            ]
        ])
        ->add('rolePerson', CollectionType::class, [
            'entry_type'   => RolePersonType::class,
            'allow_add'    => true,
            'allow_delete' => true
        ])

        ->add("comment", NULL, [
            "label" => "Commentaire",
            "attr" => [
                "rows" => 5,
                "placeholder" => "Saisir un commentaire sur le ménage"
            ]
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            "data_class" => GroupPeople::class,
            "translation_domain" => "forms",
        ]);
    }
}