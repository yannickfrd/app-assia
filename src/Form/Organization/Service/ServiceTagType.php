<?php

namespace App\Form\Organization\Service;

use App\Entity\Organization\Tag;
use App\Repository\Organization\TagRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ServiceTagType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('tag', EntityType::class, [
                'class' => Tag::class,
                'choice_label' => 'name',
                'query_builder' => function (TagRepository $repo) {
                    return $repo->createQueryBuilder('t')
                        ->orderBy('t.name', 'ASC');
                },
                'placeholder' => 'placeholder.select',
                'attr' => [
                    'class' => 'col-auto my-1',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Tag::class,
            'translation_domain' => 'forms',
        ]);
    }
}
