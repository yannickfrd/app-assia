<?php

namespace App\Form\Support\Note;

use App\Entity\Note;

use App\Form\Utils\Choices;

use Symfony\Component\Form\AbstractType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class NoteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("title", null, [
                "attr" => [
                    "placeholder" => "Title",
                    "class" => "font-weight-bold"
                ]
            ])
            ->add("content", CKEditorType::class, [
                "config" => [
                    "uiColor" => "#fafafafa",
                    // "toolbar" => "full"
                ],
                // "required" => true
            ])
            ->add("type", ChoiceType::class, [
                "choices" => Choices::getchoices(Note::TYPE),
            ])
            ->add("status", ChoiceType::class, [
                "choices" => Choices::getchoices(Note::STATUS),
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            "data_class" => Note::class,
            "translation_domain" => "forms"
        ]);
    }
}
