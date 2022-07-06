<?php

namespace App\Form\Support\Document;

use App\Entity\Organization\Service;
use App\Entity\Organization\Tag;
use App\Form\Model\Support\SupportDocumentSearch;
use App\Form\Support\Support\DeletedSearchType;
use App\Repository\Organization\TagRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SupportDocumentSearchType extends AbstractType
{
    /** @var TagRepository */
    private $tagRepo;

    public function __construct(TagRepository $tagRepo)
    {
        $this->tagRepo = $tagRepo;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Service */
        $service = $options['service'];

        $builder
            ->add('name', SearchType::class, [
                'label_attr' => ['class' => 'sr-only'],
                'attr' => ['placeholder' => 'Search'],
                'required' => false,
            ])
            ->add('tags', EntityType::class, [
                'class' => Tag::class,
                'multiple' => true,
                'expanded' => false,
                'required' => false,
                'by_reference' => false,
                'choices' => $this->tagRepo->getTagsByService($service, 'document'),
                'choice_label' => 'name',
                'label_attr' => ['class' => 'sr-only'],
                'attr' => [
                    'class' => 'multi-select w-max-220',
                    'placeholder' => 'placeholder.tags',
                    'size' => 1,
                ],
            ])
            ->add('deleted', DeletedSearchType::class, [
                'label' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SupportDocumentSearch::class,
            'method' => 'get',
            'translation_domain' => 'forms',
            'allow_extra_fields' => true,
            'csrf_protection' => false,
            'service' => null,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'search';
    }
}
