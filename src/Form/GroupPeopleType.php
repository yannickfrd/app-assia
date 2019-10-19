<?php

namespace App\Form;

use App\Entity\GroupPeople;

use App\Form\RolePersonMinType;

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
                "choices" => $this->getchoices(GroupPeople::FAMILY_TYPOLOGY),
                "placeholder" => "-- Select --",
                "required" => true
            ])
            ->add("nbPeople", null, [
                "attr" => [
                    "class" => "col-md-6"
                ],
                "required" => true
            ])
            ->add('rolePerson', CollectionType::class, [
                'entry_type'   => RolePersonMinType::class,
                'allow_add'    => false,
                'allow_delete' => false,
                "required" => true
            ])

            ->add("comment", null, [
                "attr" => [
                    "rows" => 4,
                    "placeholder" => "Write a comment about the group"
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
