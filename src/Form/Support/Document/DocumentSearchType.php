<?php

namespace App\Form\Support\Document;

use App\Entity\Organization\Tag;
use App\Form\Model\Support\DocumentSearch;
use App\Form\Type\DateSearchType;
use App\Form\Type\ServiceDeviceReferentSearchType;
use App\Repository\Organization\TagRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DocumentSearchType extends AbstractType
{
    private $tagRepo;

    public function __construct(TagRepository $tagRepo)
    {
        $this->tagRepo = $tagRepo;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('id', null, [
                'attr' => [
                    'placeholder' => 'ID',
                    'class' => 'w-max-80',
                ],
            ])
            ->add('name', null, [
                'attr' => ['placeholder' => 'Search'],
            ])
            ->add('date', DateSearchType::class, [
                'data_class' => DocumentSearch::class,
            ])
            ->add('service', ServiceDeviceReferentSearchType::class, [
                'data_class' => DocumentSearch::class,
            ])
            ->add('tags', EntityType::class, [
                'class' => Tag::class,
                'multiple' => true,
                'expanded' => false,
                'by_reference' => false,
                'choices' => $this->tagRepo->findAllTags('document'),
                'choice_label' => 'name',
                'label_attr' => ['class' => 'sr-only'],
                'attr' => [
                    'class' => 'multi-select w-max-220',
                    'placeholder' => 'placeholder.tags',
                    'size' => 1,
                ],
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DocumentSearch::class,
            'method' => 'get',
            'translation_domain' => 'forms',
            'allow_extra_fields' => true,
            'csrf_protection' => false,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}
