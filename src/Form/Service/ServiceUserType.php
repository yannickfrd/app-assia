<?php

namespace App\Form\Service;

use App\Entity\Service;
use App\Entity\ServiceUser;

use App\Security\CurrentUserService;

use App\Repository\ServiceRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
            ->add("service", EntityType::class, [
                "class" => Service::class,
                "choice_label" => "name",
                "query_builder" => function (ServiceRepository $repo) {
                    return $repo->getServicesQueryList($this->currentUser);
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
