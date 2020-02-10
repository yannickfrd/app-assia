<?php

namespace App\Form\Support\Evaluation;

use App\Entity\SitProfPerson;

use App\Form\Utils\Choices;
use App\Form\Utils\SelectList;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class SitProfPersonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("profStatus", ChoiceType::class, [
                "choices" => Choices::getChoices(SitProfPerson::STATUS),
                "placeholder" => "-- Select --",
                "required" => false
            ])

            ->add("schoolLevel", ChoiceType::class, [
                "choices" => Choices::getChoices(SitProfPerson::SCHOOL_LEVEL),
                "placeholder" => "-- Select --",
                "required" => false
            ])
            ->add("contractType", ChoiceType::class, [
                "choices" => Choices::getChoices(SitProfPerson::CONTRACT_TYPE),
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
            "data_class" => SitProfPerson::class,
            "translation_domain" => "sitProf",
        ]);
    }
}
