<?php

namespace App\Form\Person;

use App\Entity\RolePerson;

use App\Form\Group\GroupPeopleType;
use App\Form\Person\PersonType;
use App\Form\Utils\Choices;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class RolePersonGroupType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("role", ChoiceType::class, [
                "choices" => Choices::getChoices(RolePerson::ROLE),
                "placeholder" => "-- Select --",
                "required" => true
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
