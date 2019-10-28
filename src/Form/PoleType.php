<?php

namespace App\Form;

use App\Entity\Pole;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PoleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("name")
            ->add("email")
            ->add("phone")
            ->add("address")
            ->add("city")
            ->add("zipCode")
            ->add("director")
            ->add("comment")
            ->add("createdAt");
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            "data_class" => Pole::class,
            "translation_domain" => "forms",
        ]);
    }
}
