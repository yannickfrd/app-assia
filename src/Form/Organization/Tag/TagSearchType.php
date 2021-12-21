<?php

namespace App\Form\Organization\Tag;

use App\Form\Model\Organization\TagSearch;
use App\Repository\Organization\TagRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TagSearchType extends AbstractType
{
    /** @var TagRepository */
    private $tagRepo;

    public function __construct(TagRepository $tagRepo)
    {
        $this->tagRepo = $tagRepo;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', null, [
                'label_attr' => ['class' => 'sr-only'],
                'attr' => ['placeholder' => 'Search'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => TagSearch::class,
            'method' => 'get',
            'translation_domain' => 'forms',
        ]);
    }
}
