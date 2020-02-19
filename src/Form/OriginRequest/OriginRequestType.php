<?php

namespace App\Form\Evaluation;

use App\Entity\Organization;
use App\Form\Utils\Choices;
use App\Entity\OriginRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class OriginRequestType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("organization", EntityType::class, [
                "class" => Organization::class,
                "choice_label" => "name"
            ])
            ->add("orientationDate", DateType::class, [
                "widget" => "single_text",
                "required" => false
            ])
            ->add("preAdmissionDate", DateType::class, [
                "widget" => "single_text",
                "required" => false
            ])
            ->add("preAdmissionResult", ChoiceType::class, [
                "choices" => Choices::getChoices(OriginRequest::RESULT_PRE_ADMISSION),
                "placeholder" => "-- Select --",
            ])
            ->add("decisionDate", DateType::class, [
                "widget" => "single_text",
                "required" => false
            ])
            ->add("comment", TextareaType::class, [
                "label_attr" => ["class" => "sr-only"],
                "attr" => [
                    "rows" => 5,
                    "placeholder" => "Write a comment about the origin request"
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            "data_class" => OriginRequest::class,
            "translation_domain" => "originRequest"
        ]);
    }
}
