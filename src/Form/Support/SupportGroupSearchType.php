<?php

namespace App\Form\Support;

use App\Entity\GroupPeople;
use App\Entity\Service;
use App\Entity\SupportGroup;
use App\Entity\User;

use App\Form\Model\SupportGroupSearch;
use App\Form\Utils\Choices;

use App\Repository\UserRepository;
use App\Repository\ServiceRepository;

use App\Security\CurrentUserService;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class SupportGroupSearchType extends AbstractType
{
    private $currentUser;

    public function __construct(CurrentUserService $currentUser)
    {
        $this->currentUser = $currentUser;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("fullname", null, [
                "label_attr" => ["class" => "sr-only"],
                "attr" => [
                    "placeholder" => "Nom et/ou prénom",
                    "class" => "w-max-170",
                ]
            ])
            // ->add("birthdate", DateType::class, [
            //     "widget" => "single_text",
            //     "attr" => [
            //         "class" => "w-max-165",
            //         "placeholder" => "jj/mm/aaaa"
            //     ],
            //     "required" => false
            // ])
            // ->add("familyTypology", ChoiceType::class, [
            //     "placeholder" => "-- Family Typology --",
            //     "required" => false,
            //     "choices" => Choices::getChoices(GroupPeople::FAMILY_TYPOLOGY),
            //     "attr" => [
            //         "class" => "w-max-200",
            //     ]
            // ])
            // ->add("nbPeople", null, [
            //     "attr" => [
            //         "class" => "w-max-100",
            //         "placeholder" => "NbPeople",
            //         "autocomplete" => "off"
            //     ]
            // ])
            ->add("status", ChoiceType::class, [
                "label_attr" => ["class" => "sr-only"],
                "multiple" => true,
                "choices" => Choices::getChoices(SupportGroup::STATUS),
                "attr" => [
                    "class" => "multi-select js-status",
                ],
                "placeholder" => "-- Status --",
                "required" => false
            ])
            ->add("supportDates", ChoiceType::class, [
                "label_attr" => ["class" => "sr-only"],
                "choices" => Choices::getChoices(SupportGroupSearch::SUPPORT_DATES),
                "placeholder" => "-- Date de suivi --",
                "required" => false
            ])
            ->add("startDate", DateType::class, [
                "label_attr" => ["class" => "sr-only"],
                "widget" => "single_text",
                "attr" => [
                    "class" => "w-max-165",
                    "placeholder" => "jj/mm/aaaa"
                ],
                "required" => false
            ])
            ->add("endDate", DateType::class, [
                "label_attr" => ["class" => "sr-only"],
                "widget" => "single_text",
                "attr" => [
                    "class" => "w-max-165",
                    "placeholder" => "jj/mm/aaaa"
                ],
                "required" => false
            ])
            ->add("referent", EntityType::class, [
                "class" => User::class,
                "choice_label" => "fullname",
                "query_builder" => function (UserRepository $repo) {
                    return $repo->getAllUsersFromServicesQueryList($this->currentUser, null, true);
                },
                "label_attr" => ["class" => "sr-only"],
                "placeholder" => "-- Référent --",
                "attr" => [
                    "class" => "w-max-180"
                ],
                "required" => false
            ])
            ->add("service", EntityType::class, [
                "class" => Service::class,
                "choice_label" => "name",
                "multiple" => true,
                "query_builder" => function (ServiceRepository $repo) {
                    return $repo->getServicesQueryList($this->currentUser);
                },
                "label_attr" => ["class" => "sr-only"],
                "placeholder" => "-- Service --",
                "attr" => [
                    "class" => "multi-select js-service w-min-150 w-max-180"
                ],
                "required" => false
            ])
            ->add("export");
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            "data_class" => SupportGroupSearch::class,
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
