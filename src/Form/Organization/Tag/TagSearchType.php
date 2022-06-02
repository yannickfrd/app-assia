<?php

namespace App\Form\Organization\Tag;

use App\Entity\Organization\Tag;
use App\Form\Model\Organization\TagSearch;
use App\Form\Utils\Choices;
use App\Repository\Organization\TagRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
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
            ->add('color', ChoiceType::class, [
                'choices' => Choices::getChoices(Tag::COLORS),
                'placeholder' => 'placeholder.color',
                'required' => false,
            ])
            ->add('categories', ChoiceType::class, [
                'multiple' => true,
                'choices' => Choices::getChoices(Tag::CATEGORIES),
                'attr' => [
                    'class' => 'multi-select w-min-180',
                    'placeholder' => 'placeholder.categories',
                    'size' => 1,
                ],
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => TagSearch::class,
            'method' => 'get',
            'translation_domain' => 'forms',
            'csrf_protection' => false,
        ]);
    }
}