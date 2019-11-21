<?php

namespace App\Form;

use App\Form\Utils\Choices;;

use App\Entity\Service;
use App\Entity\RoleUser;

use App\Repository\ServiceRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class RoleUserType extends AbstractType
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
            ->add("role", ChoiceType::class, [
                "label" => "Fonction",
                'placeholder' => "-- Select --",
                "choices" => Choices::getChoices(RoleUser::ROLE),
                "attr" => [
                    "class" => "col-auto my-1",
                ]
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
                "attr" => [
                    "class" => "col-auto my-1",
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            "data_class" => RoleUSer::class,
            "translation_domain" => "forms",
        ]);
    }
}
