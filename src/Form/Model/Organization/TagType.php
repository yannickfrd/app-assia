<?php

namespace App\Form\Model\Organization;

use App\Entity\Organization\Service;
use App\Entity\Organization\Tag;
use App\Repository\Organization\TagRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TagType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Service */
        $service = $options['service'];

        $builder
            ->add('tags', EntityType::class, [
                'class' => Tag::class,
                'label' => false,
                'multiple' => true,
                'expanded' => false,
                'required' => false,
                'query_builder' => function (TagRepository $tagRepo) use ($service) {
                    $qb = $tagRepo->findTagByServiceQueryBuilder($service);
                    if (empty($qb->getQuery()->getResult())) {
                        $qb = $tagRepo->findAllQueryBuilder();
                    }

                    return $qb;
                },
                'block_prefix' => 'wrapped_text',
                'attr' => [
                    'class' => 'multi-select col-9',
                    'placeholder' => 'placeholder.tags',
                    'size' => 1,
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => null,
                'translation_domain' => 'forms',
            ])
            ->setRequired(['service'])
        ;
    }
}
