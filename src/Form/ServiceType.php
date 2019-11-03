<?php

namespace App\Form;

use App\Entity\Pole;
use App\Entity\Service;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
            ->add("phone")
            ->add("email", null, [
                "attr" => [
                    "placeholder" => "Email"
                ]
            ])
            ->add("address")
            ->add("city")
            ->add("zipCode")
            ->add("comment");
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            "data_class" => Service::class,
            "translation_domain" => "forms",
        ]);
    }
}
