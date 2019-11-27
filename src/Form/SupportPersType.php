<?php

namespace App\Form;

use App\Form\SitProfType;

use App\Entity\SupportPers;
use App\Form\SitBudgetType;

use App\Form\Utils\Choices;;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class SupportPersType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("startDate", DateType::class, [
                "widget" => "single_text",
                "attr" => [
                    "class" => "w-max-180",
                    "placeholder" => "jj/mm/aaaa",
                ],
                "required" => true
            ])
            ->add("endDate", DateType::class, [
                "widget" => "single_text",
                "attr" => [
                    "class" => "w-max-180",
                    "placeholder" => "jj/mm/aaaa",
                ],
                "required" => false
            ])
            ->add("status", ChoiceType::class, [
                "choices" => Choices::getChoices(SupportPers::STATUS),
                "placeholder" => "-- Select --",
                "required" => true
            ])
            ->add("sitAdm", SitAdmType::class)
            ->add("sitProf", SitProfType::class)
            ->add("sitBudget", SitBudgetType::class)
            ->add("comment", null, [
                "attr" => [
                    "rows" => 1,
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            "data_class" => SupportPers::class,
            "translation_domain" => "forms",
        ]);
    }
}
