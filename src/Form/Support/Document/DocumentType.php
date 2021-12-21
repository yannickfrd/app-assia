<?php

namespace App\Form\Support\Document;

use App\Entity\Organization\Tag;
use App\Entity\Support\Document;
use App\Entity\Support\SupportGroup;
use App\Repository\Organization\TagRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DocumentType extends AbstractType
{
    /** @var TagRepository */
    private $tagRepo;

    public function __construct(TagRepository $tagRepo)
    {
        $this->tagRepo = $tagRepo;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var SupportGroup */
        $supportGroup = $options['data']->getSupportGroup();

        $builder
            ->add('name', TextType::class, [
                'label_attr' => ['class' => 'sr-only'],
                'attr' => ['placeholder' => 'document.name.placeholder'],
            ])
            ->add('content', TextareaType::class, [
                'label_attr' => ['class' => 'sr-only'],
                'attr' => [
                    'rows' => 4,
                    'placeholder' => 'document.content.placeholder',
                ],
            ])
            ->add('tags', EntityType::class, [
                'class' => Tag::class,
                'multiple' => true,
                'expanded' => false,
                'required' => false,
                'by_reference' => false,
                'choices' => $this->tagRepo->getTagsWithOrWithoutService($supportGroup->getService()),
                'choice_label' => 'name',
                'label_attr' => ['class' => 'sr-only'],
                'attr' => [
                    'data-select2-id' => 'tags',
                    'size' => 1,
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Document::class,
            'translation_domain' => 'forms',
        ]);
    }
}
