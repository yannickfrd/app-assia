<?php

namespace App\Form;

use App\Entity\Service;

use App\Entity\ServiceUser;

use App\Form\Utils\Choices;;

use App\Security\CurrentUserService;
use App\Repository\ServiceRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class ServiceUserType extends AbstractType
{
    private $currentUser;

    public function __construct(CurrentUserService $currentUser)
    {
        $this->currentUser = $currentUser;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            // ->add("role", ChoiceType::class, [
            //     "label" => "Fonction",
            //     'placeholder' => "-- Select --",
            //     "choices" => Choices::getChoices(ServiceUser::ROLE),
            //     "attr" => [
            //         "class" => "col-auto my-1",
            //     ]
            // ])
            ->add("service", EntityType::class, [
                "class" => Service::class,
                "choice_label" => "name",
                "query_builder" => function (ServiceRepository $repo) {
                    if ($this->currentUser->isAdmin("ROLE_SUPER_ADMIN")) {
                        return $repo->createQueryBuilder("s")
                            ->orderBy("s.name", "ASC");
                    }
                    return $repo->createQueryBuilder("s")
                        ->where("s.id IN (:services)")
                        ->setParameter("services", $this->currentUser->getServices())
                        ->orderBy("s.name", "ASC");
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
            "data_class" => ServiceUser::class,
            "translation_domain" => "forms",
        ]);
    }
}
