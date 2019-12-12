<?php

namespace App\Form\Service;

use App\Entity\Pole;
use App\Entity\Service;

use App\Form\Utils\Choices;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class ServiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("name", null, [
                "attr" => [
                    "placeholder" => "Service name"
                ]
            ])
            ->add("pole", EntityType::class, [
                "class" => Pole::class,
                "choice_label" => "name",
                "placeholder" => "-- Select --",
            ])
            ->add("phone", null, [
                "attr" => [
                    "class" => "js-phone ",
                ]
            ])
            ->add("email", null, [
                "attr" => [
                    "placeholder" => "Email"
                ]
            ])
            ->add("address")
            ->add("city")
            ->add("zipCode", null, [
                "attr" => [
                    "class" => "js-zip-code ",
                ]
            ])
            ->add("supportAccess", ChoiceType::class, [
                "choices" => Choices::getChoices(Service::SUPPORT_ACCESS),
                "placeholder" => "-- Select --",
                "required" => false
            ])
            ->add("comment", null, [
                "attr" => [
                    "rows" => 5,
                    "placeholder" => "Write a comment about the service"
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            "data_class" => Service::class,
            "translation_domain" => "forms",
        ]);
    }
}
