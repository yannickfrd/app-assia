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
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('services', EntityType::class, [
                'class' => Service::class,
                'choice_label' => 'name',
                'multiple' => true,
                'query_builder' => function (ServiceRepository $repo) {
                    return $repo->createQueryBuilder('s')->orderBy('s.name', 'ASC');
                },
                'placeholder' => 'placeholder.service',
                'attr' => [
                    'class' => 'multi-select w-min-150',
                    'data-select2-id' => 'services',
                ],
                'required' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Import::class,
            'csrf_protection' => true,
            'allow_extra_fields' => true,
            'translation_domain' => 'forms',
        ]);
    }
}
