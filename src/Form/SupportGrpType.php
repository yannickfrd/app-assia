<?php

namespace App\Form;

use App\Entity\Service;
use App\Entity\SupportGrp;
use App\Form\SitSocialType;

use App\Form\SitHousingType;

use App\Form\Utils\Choices;;

use App\Form\SupportPersType;
use App\Form\SitBudgetGrpType;
use App\Form\SitFamilyGrpType;
use App\Utils\CurrentUserService;
use App\Repository\ServiceRepository;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;


class SupportGrpType extends AbstractType
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

                "choices" => Choices::getChoices(SupportGrp::STATUS),
                "placeholder" => "-- Select --",
            ])
            ->add("service", EntityType::class, [
                "class" => Service::class,
                "choice_label" => "name",
                "query_builder" => function (ServiceRepository $repo) {
                    if ($this->currentUser->isAdmin("ROLE_SUPER_ADMIN")) {
                        return $repo->createQueryBuilder("s")
                            ->orderBy("s.name", "ASC");
                    }
                    return $repo->createQueryBuilder("s")
                        ->where("s.id IN (:services)")
                        ->setParameter("services", $this->currentUser->getServices())
                        ->orderBy("s.name", "ASC");
                },
                "placeholder" => "-- Select --",
                // "attr" => ["class" => ""],
            ])
            ->add("endDate", DateType::class, [
                "widget" => "single_text",
                "required" => false
            ])
            ->add("sitSocial", SitSocialType::class)
            ->add("sitFamilyGrp", SitFamilyGrpType::class)
            ->add("sitBudgetGrp", SitBudgetGrpType::class)
            ->add("sitHousing", SitHousingType::class)
            ->add("supportPers", CollectionType::class, [
                "entry_type"   => SupportPersType::class,
                "allow_add"    => false,
                "allow_delete" => false,
                "required" => true
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
            "data_class" => SupportGrp::class,
            "translation_domain" => "forms"
        ]);
    }
}
