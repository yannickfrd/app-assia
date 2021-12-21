<?php

namespace App\Form\Support\Rdv;

use App\Entity\Organization\Tag;
use App\Entity\Support\Rdv;
use App\Form\Utils\Choices;
use App\Repository\Organization\TagRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RdvType extends AbstractType
{
    /** @var TagRepository */
    private $tagRepo;

    public function __construct(TagRepository $tagRepo)
    {
        $this->tagRepo = $tagRepo;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', null, [
                'attr' => [
                    'class' => 'font-weight-bold',
                    'placeholder' => 'rdv.placeholder.title',
                ],
            ])
            ->add('start', DateTimeType::class, [
                'widget' => 'single_text',
            ])
            ->add('end', DateTimeType::class, [
                'widget' => 'single_text',
            ])
            ->add('status', ChoiceType::class, [
                'choices' => Choices::getchoices(Rdv::STATUS),
                'placeholder' => 'placeholder.status',
                'required' => false,
            ])
            ->add('location', null, [
                'attr' => [
                    'class' => 'js-search',
                    'placeholder' => 'rdv.placeholder.location',
                    'autocomplete' => 'off',
                ],
            ])
            ->add('content', null, [
                'attr' => [
                    'rows' => 5,
                    'placeholder' => 'rdv.placeholder.content',
                ],
            ])
            ->add('tags', EntityType::class, [
                'class' => Tag::class,
                'label' => false,
                'multiple' => true,
                'expanded' => false,
                'required' => false,
                'by_reference' => false,
                'choices' => $options['data']->getSupportGroup() ?
                    $this->tagRepo->getTagsWithOrWithoutService($options['data']->getSupportGroup()->getService()) :
                    $this->tagRepo->findAllWithPartialLoadGetResult(),
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
            'data_class' => Rdv::class,
            'translation_domain' => 'forms',
        ]);
    }
}
