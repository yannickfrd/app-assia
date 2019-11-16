<?php

namespace App\Form;

use App\Entity\User;
use App\Utils\Choices;
use App\Entity\Service;
use App\Entity\RoleUser;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class RoleUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("role", ChoiceType::class, [
                'placeholder' => "-- Select --",
                "required" => false,
                "choices" => Choices::getChoices(RoleUser::ROLE),
                "attr" => [
                    "class" => "",
                ]
            ])
            ->add("service", EntityType::class, [
                "class" => Service::class,
                "choice_label" => "name",
                "placeholder" => "-- Select --"
            ]);

        // ->add("roleUser", EntityType::class, [
        //     "class" => RoleUser::class,
        //     "choice_label" => "role",
        //     "placeholder" => "-- Select --",
        //     "required" => false,
        // ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            "data_class" => RoleUSer::class,
            "translation_domain" => "forms",
        ]);
    }
}
