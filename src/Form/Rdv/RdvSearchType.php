<?php

namespace App\Form\Rdv;

use App\Entity\Rdv;
use App\Form\Utils\Choices;
use App\Form\Model\RdvSearch;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class RdvSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("content", null, [
                "label_attr" => [
                    "class" => "sr-only"
                ],
                "attr" => [
                    "placeholder" => "Search"
                ],
            ])
            ->add("status", ChoiceType::class, [
                "label_attr" => [
                    "class" => "sr-only"
                ],
                "choices" => Choices::getchoices(Rdv::STATUS),
                "attr" => [
                    "class" => "w-max-150",
                ],
                "placeholder" => "-- Statut --",
                "required" => false
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            "data_class" => RdvSearch::class,
            "translation_domain" => "forms",
            'allow_extra_fields' => true,
            "csrf_protection" => false
        ]);
    }
}
