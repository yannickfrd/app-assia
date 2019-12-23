<?php

namespace App\Form\Support\Rdv;

use App\Entity\Rdv;
use App\Entity\RdvSearch;

use App\Form\Utils\Choices;

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
                "label" => false,
                "attr" => [
                    "placeholder" => "Search"
                ],
            ])
            ->add("status", ChoiceType::class, [
                "label" => false,
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
            "translation_domain" => "forms"
        ]);
    }
}
