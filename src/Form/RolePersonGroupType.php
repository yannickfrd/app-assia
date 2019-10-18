<?php

namespace App\Form;

use App\Form\PersonType;
use App\Entity\RolePerson;
use App\Form\GroupPeopleType;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class RolePersonGroupType extends FormType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("role", ChoiceType::class, [
                "choices" => $this->getChoices(RolePerson::ROLE),
            ])
            ->add("person", PersonType::class)
            ->add("groupPeople", GroupPeopleType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => RolePerson::class,
            "translation_domain" => "forms",
        ]);
    }
}
