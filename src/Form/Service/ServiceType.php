<?php

namespace App\Form\Service;

use App\Entity\Pole;
use App\Entity\Service;

use App\Form\Utils\Choices;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

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
                    "class" => "js-phone",
                    "placeholder" => "Phone"
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
            ->add("accommodation", CheckBoxType::class, [
                "required" => false,
                "label_attr" => [
                    "class" => "custom-control-label",
                ],
                "attr" => [
                    "class" => "custom-control-input checkbox"
                ]
            ])
            ->add("comment", null, [
                "attr" => [
                    "rows" => 5,
                    "placeholder" => "Write a comment about the service"
                ]
            ])
            ->add("serviceDevices", CollectionType::class, [
                "entry_type" => ServiceDeviceType::class,
                "allow_add" => true,
                "allow_delete" => true,
                "delete_empty" => true,
                "prototype" => true,
                "by_reference" => false,
                "label_attr" => [
                    "class" => "sr-only"
                ],
                "entry_options" => [
                    "attr" => ["class" => "form-inline"],
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            "data_class" => Service::class,
            "translation_domain" => "forms"
        ]);
    }
}
