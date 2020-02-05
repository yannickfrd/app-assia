<?php

namespace App\Form\Support\Accommodation;

use App\Form\Utils\Choices;
use App\Entity\Accommodation;
use App\Entity\PersonAccommodation;
use App\Entity\GroupPeopleAccommodation;
use Symfony\Component\Form\AbstractType;
use App\Repository\AccommodationRepository;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class PersonAccommodationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
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
            ->add("commentEndReason", null, [
                "attr" => [
                    "rows" => 1,
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            "data_class" => PersonAccommodation::class,
            "translation_domain" => "forms"
        ]);
    }
}
