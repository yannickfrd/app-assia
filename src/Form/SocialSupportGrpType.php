<?php

namespace App\Form;

use App\Utils\Choices;
use App\Entity\Department;
use App\Entity\SocialSupportGrp;
use App\Form\SocialSupportPersType;
use App\Repository\DepartmentRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;


class SocialSupportGrpType extends AbstractType
{
    private $security;
    private $departments;

    public function __construct(Security $security)
    {
        $this->security = $security;

        $user = $this->security->getUser();
        foreach ($user->getroleUser() as $role) {
            $this->departments[] = $role->getDepartment()->getId();
        };
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add("startDate", DateType::class, [
                "widget" => "single_text",
            ])
            ->add("status", ChoiceType::class, [

                "choices" => Choices::getChoices(SocialSupportGrp::STATUS),
                "placeholder" => "-- Select --",
            ])
            ->add("department", EntityType::class, [
                "class" => Department::class,
                "choice_label" => "name",
                "query_builder" => function (DepartmentRepository $repo) {
                    return $repo->createQueryBuilder("d")
                        ->where("d.id IN (:departments)")
                        ->setParameter("departments", $this->departments)
                        ->orderBy("d.name", "ASC");
                },
                "placeholder" => "-- Select --",
                // "attr" => ["class" => ""],
            ])
            ->add("endDate", DateType::class, [
                "widget" => "single_text",
                "required" => false
            ])
            ->add("socialSupportPers", CollectionType::class, [
                "entry_type"   => SocialSupportPersType::class,
                "allow_add"    => false,
                "allow_delete" => false,
                "required" => true
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
            "data_class" => SocialSupportGrp::class,
            "translation_domain" => "forms"
        ]);
    }
}
