<?php

namespace App\Form\Support;

use App\Entity\User;
use App\Entity\Service;
use App\Entity\SupportGroup;

use App\Form\Model\Export;
use App\Form\Model\SupportGroupSearch;
use App\Form\Utils\Choices;

use App\Repository\UserRepository;

use App\Security\CurrentUserService;

use App\Repository\ServiceRepository;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class ExportType extends AbstractType
{
    private $currentUser;

    public function __construct(CurrentUserService $currentUser)
    {
        $this->currentUser = $currentUser;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("status", ChoiceType::class, [

                "multiple" => true,
                "choices" => Choices::getChoices(SupportGroup::STATUS),
                "attr" => [
                    "class" => "multi-select js-status",
                ],
                "placeholder" => "-- Status --",
                "required" => false
            ])
            ->add("supportDates", ChoiceType::class, [
                "choices" => Choices::getChoices(SupportGroupSearch::SUPPORT_DATES),
                "attr" => [
                    "class" => "",
                ],
                "placeholder" => "-- Date de suivi --",
                "required" => false
            ])
            ->add("startDate", DateType::class, [

                "widget" => "single_text",
                "attr" => [
                    "class" => "w-max-165",
                    "placeholder" => "jj/mm/aaaa"
                ],
                "required" => false
            ])
            ->add("endDate", DateType::class, [
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
                    if ($this->currentUser->isRole("ROLE_SUPER_ADMIN")) {
                        return $repo->createQueryBuilder("u")
                            ->orderBy("u.lastname", "ASC");
                    }
                    return $repo->createQueryBuilder("u")
                        ->select("u")
                        ->leftJoin("u.serviceUser", "r")
                        ->where("r.service IN (:services)")
                        ->setParameter("services", $this->currentUser->getServices())
                        ->orderBy("u.lastname", "ASC");
                },
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
                    if ($this->currentUser->isRole("ROLE_SUPER_ADMIN")) {
                        return $repo->createQueryBuilder("s")
                            ->orderBy("s.name", "ASC");
                    }
                    return $repo->createQueryBuilder("s")
                        ->where("s.id IN (:services)")
                        ->setParameter("services", $this->currentUser->getServices())
                        ->orderBy("s.name", "ASC");
                },
                "placeholder" => "-- Service --",
                "attr" => [
                    "class" => "multi-select js-service w-min-150 w-max-180"
                ],
                "required" => false
            ])
            ->add("sitSocial", CheckBoxType::class, [
                "required" => false,
                "label_attr" => [
                    "class" => "custom-control-label",
                ],
                "attr" => [
                    "class" => "custom-control-input checkbox"
                ]
            ])
            ->add("sitAdm", CheckBoxType::class, [
                "required" => false,
                "label_attr" => [
                    "class" => "custom-control-label",
                ],
                "attr" => [
                    "class" => "custom-control-input checkbox"
                ]
            ])
            ->add("sitFamily", CheckBoxType::class, [
                "required" => false,
                "label_attr" => [
                    "class" => "custom-control-label",
                ],
                "attr" => [
                    "class" => "custom-control-input checkbox"
                ]
            ])
            ->add("sitBudget", CheckBoxType::class, [
                "required" => false,
                "label_attr" => [
                    "class" => "custom-control-label",
                ],
                "attr" => [
                    "class" => "custom-control-input checkbox"
                ]
            ])
            ->add("sitProf", CheckBoxType::class, [
                "required" => false,
                "label_attr" => [
                    "class" => "custom-control-label",
                ],
                "attr" => [
                    "class" => "custom-control-input checkbox"
                ]
            ])
            ->add("sitHousing", CheckBoxType::class, [
                "required" => false,
                "label_attr" => [
                    "class" => "custom-control-label",
                ],
                "attr" => [
                    "class" => "custom-control-input checkbox"
                ]
            ])
            ->add("calcul", null, [
                "mapped" => false
            ])
            ->add("export", null, [
                "mapped" => false
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            "data_class" => Export::class,
            "csrf_protection" => false,
            "translation_domain" => "support",
        ]);
    }

    public function getBlockPrefix()
    {
        return "";
    }
}
