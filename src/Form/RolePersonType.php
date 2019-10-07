<?php

namespace App\Form;

use App\Entity\RolePerson;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class RolePersonType extends FormType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('head', CheckBoxType::class, [
                "label" => "Demandeur principal",
                "required" => false
            ])
            ->add("role", ChoiceType::class, [
                "choices" => $this->getChoices(RolePerson::ROLE),
            ])
            ->add("person", PersonType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => RolePerson::class,
            "translation_domain" => "forms",
        ]);
    }
}
