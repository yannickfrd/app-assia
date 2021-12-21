<?php

namespace App\Form\Organization\Tag;

use App\Entity\Organization\Service;
use App\Entity\Organization\Tag;
use App\Repository\Organization\TagRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ServiceTagType extends AbstractType
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
            ->add('tags', EntityType::class, [
                'class' => Tag::class,
                'choice_label' => 'name',
                'label' => false,
                'multiple' => true,
                'expanded' => false,
                'by_reference' => false,
                'choices' => $this->tagRepo->findAllWithPartialLoadGetResult(),
                'attr' => [
                    'class' => 'multi-select w-min-150',
                    'data-select2-id' => 'tags',
                    'size' => 1,
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Service::class,
        ]);
    }
}
