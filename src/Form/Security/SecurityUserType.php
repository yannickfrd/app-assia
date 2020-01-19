<?php

namespace App\Form\Security;

use App\Entity\User;

use App\Form\Utils\Choices;
use App\Form\Service\ServiceUserType;

use App\Security\CurrentUserService;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class SecurityUserType extends AbstractType
{
    private $currentUser;

    public function __construct(CurrentUserService $currentUser)
    {
        $this->currentUser = $currentUser;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("firstname", null, [
                "attr" => [
                    "class" => "text-capitalize",
                    "placeholder" => "Firstname",
                    "autocomplete" => "off"
                ],
            ])
            ->add("lastname", null, [
                "attr" => [
                    "class" => "text-capitalize",
                    "placeholder" => "Lastname",
                    "autocomplete" => "off"
                ]
            ])
            ->add("username", null, [
                "attr" => [
                    "placeholder" => "Login",
                    "autocomplete" => "off"
                ],
                "help" => "Remplissage auto. en fonction du nom et prénom.",
            ])
            ->add("email", null, [
                "attr" => [
                    "placeholder" => "Email"
                ]
            ])
            ->add("phone", null, [
                "attr" => [
                    "class" => "js-phone",
                    "placeholder" => "Phone"
                ]
            ])
            ->add("status", ChoiceType::class, [
                "choices" => Choices::getChoices(User::STATUS),
                "label" => "Fonction",
                "placeholder" => "-- Select --",
                "required" => true
            ])
            ->add("roles", ChoiceType::class, [
                "choices" => $this->getRoles(),
                "label" => "Rôle",
                "multiple" => true,
                "attr" => ["class" => "h-max-76"],
                "placeholder" => "-- Select --",
            ])
            ->add("serviceUser", CollectionType::class, [
                "entry_type" => ServiceUserType::class,
                "label_attr" => [
                    "class" => "sr-only"
                ],
                "allow_add" => true,
                "allow_delete" => true,
                "delete_empty" => true,
                "prototype" => true,
                "by_reference" => false,
                "entry_options" => [
                    "attr" => ["class" => "form-inline"],
                ]
            ]);
    }

    public function getRoles()
    {
        if ($this->currentUser->isRole("ROLE_SUPER_ADMIN")) {

            return Choices::getChoices(User::ROLES);
        }
        return [
            "Utilisateur" => "ROLE_USER",
            "Administrateur" => "ROLE_ADMIN"
        ];
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            "data_class" => User::class,
            "translation_domain" => "forms",
        ]);
    }
}
