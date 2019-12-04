<?php

namespace App\Form;

use App\Entity\RolePerson;
use App\Form\Utils\Choices;

use App\Form\GroupPeopleType2;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class PersonNewGroupType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("role", ChoiceType::class, [
                "choices" => Choices::getChoices(RolePerson::ROLE),
                "placeholder" => "-- Select --",
            ])
            ->add("groupPeople", GroupPeopleType2::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => RolePerson::class,
            "translation_domain" => "forms",
        ]);
    }
}
