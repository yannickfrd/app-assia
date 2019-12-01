<?php

namespace App\Form;

use App\Entity\SitProf;

use App\Form\Utils\Choices;;

use App\Form\Utils\SelectList;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class SitProfType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("profStatus", ChoiceType::class, [
                "choices" => Choices::getChoices(SitProf::STATUS),
                "placeholder" => "-- Select --",
                "required" => false
            ])

            ->add("schoolLevel", ChoiceType::class, [
                "choices" => Choices::getChoices(SitProf::SCHOOL_LEVEL),
                "placeholder" => "-- Select --",
                "required" => false
            ])
            ->add("contractType", ChoiceType::class, [
                "choices" => Choices::getChoices(SitProf::CONTRACT_TYPE),
                "placeholder" => "-- Select --",
                "required" => false
            ])
            ->add("jobType")
            ->add("contractStartDate", DateType::class, [
                "widget" => "single_text",
                "attr" => [
                    "placeholder" => "jj/mm/aaaa",
                ],
                "required" => false
            ])
            ->add("contractEndDate", DateType::class, [
                "widget" => "single_text",
                "attr" => [
                    "placeholder" => "jj/mm/aaaa",
                ],
                "required" => false
            ])
            ->add("nbWorkingHours")
            ->add("workingHours")
            ->add("workPlace")
            ->add("employerName")
            ->add("transportMeans")
            ->add("rqth", ChoiceType::class, [
                "choices" => Choices::getChoices(SelectList::YES_NO),
                "placeholder" => "-- Select --",
                "required" => false
            ])
            ->add("commentSitProf", null, [
                "label_attr" => ["class" => "col-sm-12"],
                "attr" => [
                    "rows" => 5,
                    "placeholder" => "Write a comment about the professional situation"
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            "data_class" => SitProf::class,
            "translation_domain" => "sitProf",
        ]);
    }
}
