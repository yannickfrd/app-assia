<?php

namespace App\Form\Admin;

use App\Entity\Organization\Service;
use App\Form\Model\Admin\Import;
use App\Repository\Organization\ServiceRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ImportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('services', EntityType::class, [
                'class' => Service::class,
                'choice_label' => 'name',
                'multiple' => true,
                'query_builder' => function (ServiceRepository $repo) {
                    return $repo->createQueryBuilder('s')->orderBy('s.name', 'ASC');
                },
                'attr' => [
                    'class' => 'w-max-220',
                    'placeholder' => 'placeholder.services',
                    'size' => 1,
                ],
                'required' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Import::class,
            'csrf_protection' => true,
            'allow_extra_fields' => true,
            'translation_domain' => 'forms',
        ]);
    }
}
