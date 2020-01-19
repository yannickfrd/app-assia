<?php

namespace App\Form\User;

use App\Entity\Pole;
use App\Entity\Service;
use App\Entity\ServiceUser;
use App\Entity\User;
use App\Entity\UserSearch;

use App\Form\Utils\Choices;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class UserSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("lastname", null, [
                "label_attr" => [
                    "class" => "sr-only"
                ],
                "attr" => [
                    "class" => "w-max-140 text-uppercase",
                    "placeholder" => "Lastname",
                ]
            ])
            ->add("firstname", null, [
                "label_attr" => [
                    "class" => "sr-only"
                ],
                "attr" => [
                    "class" => "w-max-140 text-capitalize",
                    "placeholder" => "Firstname",
                ]
            ])
            ->add("status", ChoiceType::class, [
                "choices" => Choices::getChoices(User::STATUS),
                "label_attr" => [
                    "class" => "sr-only"
                ],
                "placeholder" => "-- Fonction --",
                "required" => false

            ])
            ->add("serviceUser", ChoiceType::class, [
                "choices" => Choices::getChoices(ServiceUser::ROLE),
                "label_attr" => [
                    "class" => "sr-only"
                ],
                "attr" => [
                    "class" => "w-max-120",
                ],
                "placeholder" => "-- Rôle --",
                "required" => false,
            ])
            ->add("phone", null, [
                "label_attr" => [
                    "class" => "sr-only"
                ],
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
                "label_attr" => [
                    "class" => "sr-only"
                ],
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
                "label_attr" => [
                    "class" => "sr-only"
                ],
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
