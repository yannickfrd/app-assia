<?php

namespace App\Form\Support;

use App\Entity\User;
use App\Entity\Device;
use App\Entity\Service;

use App\Form\Utils\Choices;
use App\Entity\SupportGroup;
use App\Entity\ServiceDevice;
use App\Repository\UserRepository;
use App\Form\Support\SupportSitType;
use App\Security\CurrentUserService;

use App\Repository\ServiceRepository;

use Symfony\Component\Form\AbstractType;
use App\Repository\ServiceDeviceRepository;

use App\Form\Support\Evaluation\SitSocialType;
use App\Form\Support\Evaluation\SitHousingType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Form\Support\Evaluation\SitBudgetGroupType;
use App\Form\Support\Evaluation\SitFamilyGroupType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;


class SupportGroupType extends AbstractType
{
    private $currentUser;

    public function __construct(CurrentUserService $currentUser)
    {
        $this->currentUser = $currentUser;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add("startDate", DateType::class, [
                "widget" => "single_text",
            ])
            ->add("status", ChoiceType::class, [
                "choices" => Choices::getChoices(SupportGroup::STATUS),
                "placeholder" => "-- Select --",
            ])
            ->add("service", EntityType::class, [
                "class" => Service::class,
                "choice_label" => "name",
                "query_builder" => function (ServiceRepository $repo) {
                    if ($this->currentUser->isRole("ROLE_SUPER_ADMIN")) {
                        return $repo->createQueryBuilder("s")
                            ->orderBy("s.name", "ASC");
                    }
                    return $repo->createQueryBuilder("s")
                        ->where("s.id IN (:services)")
                        ->setParameter("services", $this->currentUser->getServices())
                        ->orderBy("s.name", "ASC");
                }
            ])
            // ->add("device", EntityType::class, [
            //     "class" => ServiceDevice::class,
            //     "mapped" => false,
            //     "choice_label" => "device.name",
            //     "query_builder" => function (ServiceDeviceRepository $repo) {
            //         return $repo->createQueryBuilder("sd")
            //             // ->where("sd.service IN (:services)")
            //             // ->setParameter("services", $this->currentUser->getServices())
            //             ->orderBy("sd.device", "ASC");
            //     }
            // ])
            ->add("referent", EntityType::class, [
                "class" => User::class,
                "choice_label" => "fullname",
                "query_builder" => function (UserRepository $repo) {
                    if ($this->currentUser->isRole("ROLE_SUPER_ADMIN")) {
                        return $repo->createQueryBuilder("u")
                            ->orderBy("u.lastname", "ASC");
                    } else if ($this->currentUser->isRole("ROLE_ADMIN")) {
                        return $repo->createQueryBuilder("u")
                            ->select("u")
                            ->leftJoin("u.serviceUser", "r")
                            ->where("r.service IN (:services)")
                            ->setParameter("services", $this->currentUser->getServices())
                            ->orderBy("u.lastname", "ASC");
                    }
                    return $repo->createQueryBuilder("u")
                        ->select("u")
                        ->where("u.id = :user")
                        ->setParameter("user", $this->currentUser->getUser())
                        ->orderBy("u.lastname", "ASC");
                },
            ])
            ->add("referent2", EntityType::class, [
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
                "placeholder" => "-- Select --",
                "required" => false
            ])
            ->add("endDate", DateType::class, [
                "widget" => "single_text",
                "required" => false
            ])
            ->add("sitSocial", SitSocialType::class)
            ->add("sitFamilyGroup", SitFamilyGroupType::class)
            ->add("sitBudgetGroup", SitBudgetGroupType::class)
            ->add("sitHousing", SitHousingType::class)
            ->add("supportPerson", CollectionType::class, [
                "entry_type"   => SupportSitType::class,
                "allow_add"    => false,
                "allow_delete" => false,
                "required" => false
            ])
            ->add("comment", null, [
                "attr" => [
                    "rows" => 5,
                    "placeholder" => "Write a comment about the social support"
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            "data_class" => SupportGroup::class,
            "translation_domain" => "forms"
        ]);
    }
}
