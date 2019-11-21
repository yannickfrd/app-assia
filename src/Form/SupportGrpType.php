<?php

namespace App\Form;

use App\Entity\Service;
use App\Entity\SupportGrp;
use App\Form\SitSocialType;

use App\Form\Utils\Choices;;

use App\Form\SupportPersType;
use App\Form\SitFamilyGrpType;
use App\Repository\ServiceRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;


class SupportGrpType extends AbstractType
{
    private $security;
    private $user;
    private $services;

    public function __construct(Security $security)
    {
        $this->security = $security;

        $this->user = $this->security->getUser();
        foreach ($this->user->getroleUser() as $role) {
            $this->services[] = $role->getService()->getId();
        };
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
                    if (in_array("ROLE_SUPER_ADMIN", $this->user->getRoles())) {
                        return $repo->createQueryBuilder("s")
                            ->orderBy("s.name", "ASC");
                    } else {
                        return $repo->createQueryBuilder("s")
                            ->where("s.id IN (:services)")
                            ->setParameter("services", $this->services)
                            ->orderBy("s.name", "ASC");
                    }
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
