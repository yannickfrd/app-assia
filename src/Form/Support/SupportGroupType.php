<?php

namespace App\Form\Support;

use App\Entity\User;
use App\Entity\Service;
use App\Form\Utils\Choices;
use App\Entity\SupportGroup;
use App\Repository\UserRepository;
use App\Security\CurrentUserService;
use App\Repository\ServiceRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class SupportGroupType extends AbstractType
{
    private $currentUser;
    private $data;

    public function __construct(CurrentUserService $currentUser)
    {
        $this->currentUser = $currentUser;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->data = $options["data"];

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
                    return $repo->getServicesQueryList($this->currentUser);
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
                    return $repo->getUsersQueryList($this->currentUser, $this->data->getReferent());
                },
            ])
            ->add("referent2", EntityType::class, [
                "class" => User::class,
                "choice_label" => "fullname",
                "query_builder" => function (UserRepository $repo) {
                    return $repo->getUsersQueryList($this->currentUser, $this->data->getReferent2());
                },
                "placeholder" => "-- Select --",
                "required" => false
            ])
            ->add("endDate", DateType::class, [
                "widget" => "single_text",
                "required" => false
            ])
            ->add("agreement", CheckboxType::class, [
                "required" => true,
                "label_attr" => [
                    "class" => "custom-control-label",
                ],
                "attr" => [
                    "class" => "custom-control-input checkbox"
                ]
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
