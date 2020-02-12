<?php

namespace App\Form\Accommodation;

use App\Entity\Accommodation;
use App\Entity\Device;
use App\Repository\DeviceRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;

class AccommodationType extends AbstractType
{
    protected $place;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->place = $options["data"];

        $builder
            ->add("name", null, [
                "attr" => [
                    "placeholder" => "Nom du groupe de places"
                ]
            ])
            ->add("device", EntityType::class, [
                "class" => Device::class,
                "choice_label" => "name",
                "query_builder" => function (DeviceRepository $repo) {
                    return $repo->getDevicesQueryList($this->place);
                },
                "placeholder" => "-- Select --"
            ])
            ->add("placesNumber")
            ->add("openingDate", DateType::class, [
                "widget" => "single_text",
            ])
            ->add("closingDate", DateType::class, [
                "widget" => "single_text",
                "required" => false
            ])
            ->add("city")
            ->add("department", null, [
                "attr" => [
                    "class" => "js-dept-code"
                ]
            ])
            ->add("address")
            ->add("comment", null, [
                "attr" => [
                    "rows" => 5,
                    "placeholder" => "Description..."
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            "data_class" => Accommodation::class,
            "translation_domain" => "forms"
        ]);
    }
}
