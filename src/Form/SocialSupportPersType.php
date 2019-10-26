<?php

namespace App\Form;

use App\Entity\SocialSupportPers;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class SocialSupportPersType extends FormType
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
                "choices" => $this->getchoices(SocialSupportPers::STATUS),
                "placeholder" => "-- Select --",
                "required" => true
            ])
            ->add("comment", null, [
                "attr" => [
                    "rows" => 1,
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            "data_class" => SocialSupportPers::class,
            "translation_domain" => "forms",
        ]);
    }
}
