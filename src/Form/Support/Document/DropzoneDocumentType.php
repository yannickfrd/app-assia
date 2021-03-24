<?php

namespace App\Form\Support\Document;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\File;

class DropzoneDocumentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('files', FileType::class, [
                'multiple' => true,
                'mapped' => false,
                'constraints' => [
                    new Count(['max' => 10]),
                    new All([
                        new File([
                            'mimeTypes' => [
                                'text/plain',
                                'text/csv',
                                'application/txt',
                                'application/pdf',
                                'application/x-pdf',
                                'image/jpeg',
                                'image/png',
                                'application/msword',
                                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                'application/x-rar-compressed',
                                'application/zip',
                                'application/vnd.ms-excel',
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                'application/vnd.oasis.opendocument.spreadsheet',
                                'application/vnd.oasis.opendocument.text',
                                'application/vnd.oasis.opendocument.presentation',
                            ],
                            'maxSize' => '6M',
                            'mimeTypesMessage' => 'Merci de télécharger un fichier au format valide (doc, docx, jpg, jpeg, pdf, png, rar, xls, xlsx, zip).',
                        ]),
                    ]),
                ],
                'attr' => ['class' => 'cursor-pointer'],
                'help' => 'document.file.help',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'forms',
        ]);
    }
}
