<?php

namespace App\Form\Support\Note;

use App\Entity\Organization\Service;
use App\Entity\Organization\Tag;
use App\Entity\Support\Note;
use App\Form\Model\Support\SupportNoteSearch;
use App\Form\Support\Support\DeletedSearchType;
use App\Form\Utils\Choices;
use App\Repository\Organization\TagRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SupportNoteSearchType extends AbstractType
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
            ->add('noteId', SearchType::class, [
                'required' => false,
            ])
            ->add('content', SearchType::class, [
                'attr' => ['placeholder' => 'Search'],
                'required' => false,
            ])
            ->add('type', ChoiceType::class, [
                'choices' => Choices::getChoices(Note::TYPE),
                'attr' => ['class' => 'w-max-150'],
                'placeholder' => 'placeholder.type',
                'required' => false,
            ])
            ->add('status', ChoiceType::class, [
                'choices' => Choices::getChoices(Note::STATUS),
                'attr' => ['class' => 'w-max-150'],
                'placeholder' => 'placeholder.status',
                'required' => false,
            ])
            ->add('export')
            ->add('tags', EntityType::class, [
                'class' => Tag::class,
                'multiple' => true,
                'expanded' => false,
                'required' => false,
                'by_reference' => false,
                'choices' => $this->tagRepo->getTagsByService($service, 'note'),
                'choice_label' => 'name',
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
            'data_class' => SupportNoteSearch::class,
            'method' => 'get',
            'translation_domain' => 'forms',
            'allow_extra_fields' => true,
            'csrf_protection' => false,
            'service' => null,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}
