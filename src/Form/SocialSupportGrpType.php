<?php

namespace App\Form;

use App\Utils\Choices;
use App\Entity\Service;
use App\Entity\SocialSupportGrp;
use App\Form\SocialSupportPersType;
use App\Repository\ServiceRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;


class SocialSupportGrpType extends AbstractType
{
    private $security;
    private $services;

    public function __construct(Security $security)
    {
        $this->security = $security;

        $user = $this->security->getUser();
        foreach ($user->getroleUser() as $role) {
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

                "choices" => Choices::getChoices(SocialSupportGrp::STATUS),
                "placeholder" => "-- Select --",
            ])
            ->add("service", EntityType::class, [
                "class" => Service::class,
                "choice_label" => "name",
                "query_builder" => function (ServiceRepository $repo) {
                    return $repo->createQueryBuilder("s")
                        ->where("s.id IN (:services)")
                        ->setParameter("services", $this->services)
                        ->orderBy("s.name", "ASC");
                },
                "placeholder" => "-- Select --",
                // "attr" => ["class" => ""],
            ])
            ->add("endDate", DateType::class, [
                "widget" => "single_text",
                "required" => false
            ])
            ->add("socialSupportPers", CollectionType::class, [
                "entry_type"   => SocialSupportPersType::class,
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
            "data_class" => SocialSupportGrp::class,
            "translation_domain" => "forms"
        ]);
    }
}
