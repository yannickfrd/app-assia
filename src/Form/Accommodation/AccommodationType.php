<?php

namespace App\Form\Accommodation;

use App\Entity\Device;
use App\Entity\Service;
use App\Form\Utils\Choices;
use App\Entity\Accommodation;
use App\Repository\DeviceRepository;
use App\Security\CurrentUserService;
use App\Repository\ServiceRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class AccommodationType extends AbstractType
{
    protected $currentUser;
    protected $place;
    protected $data;

    public function __construct(CurrentUserService $currentUser)
    {
        $this->currentUser = $currentUser;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->place = $options["data"];

        $builder
            ->add("name", null, [
                "attr" => [
                    "placeholder" => "Nom du groupe de places"
                ]
            ])
            ->add("service", EntityType::class, [
                "class" => Service::class,
                "choice_label" => "name",
                "query_builder" => function (ServiceRepository $repo) {
                    return $repo->getServicesFromUserQueryList($this->currentUser);
                },
                "placeholder" => "-- Select --"
            ])
            ->add("device", EntityType::class, [
                "class" => Device::class,
                "choice_label" => "name",
                "query_builder" => function (DeviceRepository $repo) {
                    return $repo->getDevicesFromServiceQueryList($this->place);
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
                    "class" => "js-zip-code ",
                ]
            ])
            ->add("accommodationType", ChoiceType::class, [
                "choices" => Choices::getChoices(Accommodation::ACCOMMODATION_TYPE),
                "placeholder" => "-- Select --",
                "help" => "Chambre, T1, T2, T3...",
                "required" => false
            ])
            ->add("configuration", ChoiceType::class, [
                "choices" => Choices::getChoices(Accommodation::CONFIGURATION),
                "placeholder" => "-- Select --",
                "help" => "Diffus ou regroupé",
                "required" => false
            ])
            ->add("individualCollective", ChoiceType::class, [
                "choices" => Choices::getChoices(Accommodation::INDIVIDUAL_COLLECTIVE),
                "placeholder" => "-- Select --",
                "required" => false
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
