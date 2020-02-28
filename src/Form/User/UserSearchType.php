<?php

namespace App\Form\User;

use App\Entity\Pole;
use App\Entity\Service;
use App\Entity\ServiceUser;
use App\Entity\User;

use App\Form\Model\UserSearch;
use App\Form\Utils\Choices;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

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
            ])
            ->add("enabled", CheckBoxType::class, [
                "required" => false,
                "label_attr" => [
                    "class" => "custom-control-label",
                ],
                "attr" => [
                    "class" => "custom-control-input checkbox"
                ]
            ])
            ->add("export");
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            "data_class" => UserSearch::class,
            "method" => "get",
            "translation_domain" => "forms",
            'allow_extra_fields' => true,
            "csrf_protection" => false
        ]);
    }

    public function getBlockPrefix()
    {
        return "";
    }
}
