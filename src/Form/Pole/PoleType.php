<?php

namespace App\Form\Pole;

use App\Entity\Pole;
use App\Form\Utils\Choices;;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class PoleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("name")
            ->add("email")
            ->add("phone", null, [
                "attr" => [
                    "class" => "js-phone",
                ]
            ])
            ->add("address")
            ->add("city")
            ->add("zipCode", null, [
                "attr" => [
                    "class" => "js-zip-code ",
                ]
            ])
            ->add("director")
            ->add("comment")
            ->add("color", ChoiceType::class, [
                "choices" => Choices::getChoices(Pole::COLOR),
                "placeholder" => "-- Select --",
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            "data_class" => Pole::class,
            "translation_domain" => "forms",
        ]);
    }
}
