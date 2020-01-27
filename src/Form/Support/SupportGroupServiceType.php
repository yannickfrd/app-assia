<?php

namespace App\Form;

use App\Entity\Pole;
use App\Entity\Device;

use App\Entity\Service;

use App\Form\Utils\Choices;
use App\Entity\ServiceDevice;
use App\Repository\DeviceRepository;
use App\Security\CurrentUserService;
use Symfony\Component\Form\AbstractType;
use App\Repository\ServiceDeviceRepository;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class SupportGroupServiceType extends AbstractType
{
    private $currentUser;

    public function __construct()
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("name", EntityType::class, [
                "class" => Service::class,
                "choice_label" => "name",
            ]);
        // ->add("serviceDevices", EntityType::class, [
        //     "class" => Device::class,
        //     "choice_label" => "name",
        //     "query_builder" => function (DeviceRepository $repo) {
        //         return $repo->createQueryBuilder("d")
        //             // ->where("d.id IN (:services)")
        //             // ->setParameter("services", $this->currentUser->getServices())
        //             ->orderBy("d.name", "ASC");
        //     }
        // ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            "data_class" => Service::class,
            "translation_domain" => "forms"
        ]);
    }
}
