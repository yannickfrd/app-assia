<?php

namespace App\Form;

use App\Entity\Pole;
use App\Form\Utils\Choices;;

use App\Entity\ServiceUser;
use App\Entity\Service;
use App\Entity\UserSearch;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class UserSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("lastname", null, [
                "label" => false,
                "attr" => [
                    "class" => "w-max-140 text-uppercase",
                    "placeholder" => "Nom",
                    "autocomplete" => "off"
                ]
            ])
            ->add("firstname", null, [
                "label" => false,
                "attr" => [
                    "class" => "w-max-140 text-capitalize",
                    "placeholder" => "Prénom",
                    "autocomplete" => "off"
                ]
            ])
            ->add("serviceUser", ChoiceType::class, [
                'placeholder' => "-- Rôle --",
                "label" => false,
                "required" => false,
                "choices" => Choices::getChoices(ServiceUser::ROLE),
                "attr" => [
                    "class" => "w-max-120",
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
            ->add("service", EntityType::class, [
                "class" => Service::class,
                "choice_label" => "name",
                "multiple" => true,
                // "checkboxes", true,
                "label" => false,
                "placeholder" => "-- Service --",
                "attr" => [
                    "class" => "multi-select js-service"
                ],
                "required" => false

            ])
            ->add("pole", EntityType::class, [
                "class" => Pole::class,
                "choice_label" => "name",
                // "multiple" => true,
                "label" => false,
                "placeholder" => "-- Pole --",
                "required" => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            "data_class" => UserSearch::class,
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
