<?php

namespace App\Form\Support\Note;

use App\Entity\Organization\Tag;
use App\Entity\Support\Note;
use App\Entity\Support\SupportGroup;
use App\Form\Utils\Choices;
use App\Repository\Organization\TagRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NoteType extends AbstractType
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
            ->add('title', null, [
                'attr' => [
                    'placeholder' => 'Title',
                    'class' => 'font-weight-bold',
                ],
            ])
            ->add('content', null, [
                'attr' => [
                    'class' => 'd-none',
                ],
            ])
            ->add('type', ChoiceType::class, [
                'choices' => Choices::getchoices(Note::TYPE),
                'placeholder' => 'placeholder.type',
            ])
            ->add('status', ChoiceType::class, [
                'choices' => Choices::getchoices(Note::STATUS),
                'placeholder' => 'placeholder.status',
            ])
            ->add('tags', EntityType::class, [
                'class' => Tag::class,
                'multiple' => true,
                'expanded' => false,
                'required' => false,
                'by_reference' => false,
                'choices' => $this->tagRepo->getTagsWithOrWithoutService($supportGroup->getService()),
                'choice_label' => 'name',
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
            'data_class' => Note::class,
            'translation_domain' => 'forms',
        ]);
    }
}
