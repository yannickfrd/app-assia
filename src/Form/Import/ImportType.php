<?php

namespace App\Form\Import;

use App\Entity\Service;
use App\Form\Model\Import;
use App\Repository\ServiceRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ImportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('service', EntityType::class, [
                'class' => Service::class,
                'choice_label' => 'name',
                'query_builder' => function (ServiceRepository $repo) {
                    return $repo->createQueryBuilder('s')->select('PARTIAL s.{id, name}')
                    ->orderBy('s.name', 'ASC');
                },
                'placeholder' => '-- Select --',
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
