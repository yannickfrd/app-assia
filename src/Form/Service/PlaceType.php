<?php

namespace App\Form\Service;

use App\Entity\Place;
use App\Entity\Device;
use App\Entity\Service;
use App\Entity\ServiceDevice;
use App\Repository\DeviceRepository;
use Symfony\Component\Form\AbstractType;
use App\Repository\ServiceDeviceRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;

class PlaceType extends AbstractType
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
                    return $repo->createQueryBuilder("d")
                        ->select("d")
                        ->leftJoin("d.serviceDevices", "s")
                        ->where("s.service = :service")
                        ->setParameter("service", $this->place->getService())
                        ->orderBy("d.name", "ASC");
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
            "data_class" => Place::class,
            "translation_domain" => "forms"
        ]);
    }
}
