<?php

namespace App\Form\Support\Document;

use App\Entity\Document;

use App\Form\Utils\Choices;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class DocumentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("name", null, [
                "attr" => [
                    "placeholder" => "Nom du fichier",
                ]
            ])
            ->add("content", null, [
                "attr" => [
                    "rows" => 4,
                    "placeholder" => "Ajouter une description"
                ]
            ])
            ->add("type", ChoiceType::class, [
                "choices" => Choices::getchoices(Document::TYPE),
                "placeholder" => "-- Type --"
            ])
            ->add("file", FileType::class, [
                "mapped" => false,
                "constraints" => [
                    new File([
                        "maxSize" => "5M",
                        "mimeTypes" => [
                            "application/pdf",
                            "application/x-pdf",
                            "image/jpeg",
                            "image/png",
                            "application/msword",
                            "application/vnd.openxmlformats-officedocument.wordprocessingml.document"
                        ],
                        "mimeTypesMessage" => "Merci de télécharger un document valide (pdf, jpg, png, doc, docx).",
                    ])
                ],
                "attr" => [
                    "placeholder" => "Choisir un fichier..."
                ],
                "help" => "Fichier de 5Mo maximum. Formats autorisés : pdf, jpg, png, doc, docx.",
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            "data_class" => Document::class,
            "translation_domain" => "forms"
        ]);
    }
}
