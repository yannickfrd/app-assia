<?php

namespace App\Form\Support\Note;

use App\Entity\Note;
use App\Entity\NoteSearch;

use App\Form\Utils\Choices;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class NoteSearchType extends AbstractType
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
            ->add("type", ChoiceType::class, [
                "label" => false,
                "choices" => Choices::getchoices(Note::TYPE),
                "attr" => [
                    "class" => "w-max-150",
                ],
                "placeholder" => "-- Type --",
                "required" => false
            ])
            ->add("status", ChoiceType::class, [
                "label" => false,
                "choices" => Choices::getchoices(Note::STATUS),
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
            "data_class" => NoteSearch::class,
            "translation_domain" => "forms"
        ]);
    }
}
