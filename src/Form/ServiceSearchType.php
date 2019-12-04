<?php

namespace App\Form;

use App\Entity\Pole;
use App\Form\Utils\Choices;;

use App\Entity\ServiceUser;
use App\Entity\ServiceSearch;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class ServiceSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("name", null, [
                "label" => false,
                "attr" => [
                    "class" => "w-max-200",
                    "placeholder" => "Service name",
                    "autocomplete" => "off"
                ]
            ])
            ->add("city", null, [
                "label" => false,
                "attr" => [
                    "class" => "w-max-160",
                    "placeholder" => "City",
                    "autocomplete" => "off"
                ]
            ])
            ->add("phone", null, [
                "label" => false,
                "attr" => [
                    "placeholder" => "Phone",
                    "class" => "js-phone w-max-140",
                    "autocomplete" => "off"
                ],
            ])
            ->add("pole", EntityType::class, [
                "class" => Pole::class,
                "choice_label" => "name",
                "label" => false,
                "placeholder" => "-- Pole --",
                "required" => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            "data_class" => ServiceSearch::class,
            "method" => "get",
            "translation_domain" => "forms",
            "csrf_protection" => false
        ]);
    }

    public function getBlockPrefix()
    {
        return "";
    }
}
