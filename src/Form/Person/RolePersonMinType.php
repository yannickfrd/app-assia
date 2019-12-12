<?php

namespace App\Form\Person;

use App\Entity\RolePerson;

use App\Form\Utils\Choices;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class RolePersonMinType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("head", CheckBoxType::class, [
                "label" => false,
                "required" => false,
                "label_attr" => [
                    "class" => "custom-control-label",
                ],
                "attr" => [
                    "class" => "custom-control-input checkbox"
                ]
            ])
            ->add("role", ChoiceType::class, [
                "choices" => Choices::getChoices(RolePerson::ROLE),
                "placeholder" => "-- Select --",
                "required" => true
            ])
            ->add("person", PersonMinType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            "data_class" => RolePerson::class,
            "translation_domain" => "forms",
        ]);
    }
}
