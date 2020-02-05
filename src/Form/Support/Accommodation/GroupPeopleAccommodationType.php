<?php

namespace App\Form\Support\Accommodation;

use App\Form\Utils\Choices;
use App\Entity\Accommodation;
use App\Entity\GroupPeopleAccommodation;
use Symfony\Component\Form\AbstractType;
use App\Repository\AccommodationRepository;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use App\Form\Support\Accommodation\PersonAccommodationType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class GroupPeopleAccommodationType extends AbstractType
{
    protected $service;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $groupPeopleAccommodation = $options["data"];
        $this->service = $groupPeopleAccommodation->getSupportGroup()->getService();

        $builder
            ->add("accommodation", EntityType::class, [
                "class" => Accommodation::class,
                "choice_label" => "name",
                "query_builder" => function (AccommodationRepository $repo) {
                    return $repo->createQueryBuilder("a")
                        ->select("a")
                        ->where("a.service = :service")
                        ->setParameter("service", $this->service)
                        ->orderBy("a.name", "ASC");
                },
                "placeholder" => "-- Select --"
            ])
            ->add("startDate", DateType::class, [
                "widget" => "single_text",
            ])
            ->add("endDate", DateType::class, [
                "widget" => "single_text",
                "required" => false
            ])
            ->add("endReason", ChoiceType::class, [
                "choices" => Choices::getChoices(GroupPeopleAccommodation::END_REASON),
                "required" => false,
                "placeholder" => "-- Select --",

            ])
            ->add("commentEndReason")
            ->add("personAccommodations", CollectionType::class, [
                "entry_type"   => PersonAccommodationType::class,
                "label_attr" => [
                    "class" => "sr-only"
                ],
                "allow_add" => true,
                "allow_delete" => true,
                "delete_empty" => true,
                "required" => false
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            "data_class" => GroupPeopleAccommodation::class,
            "translation_domain" => "forms"
        ]);
    }
}
