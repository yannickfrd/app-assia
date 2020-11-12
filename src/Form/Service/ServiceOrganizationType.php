<?php

namespace App\Form\Service;

use App\Entity\Organization;
use App\Repository\OrganizationRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ServiceOrganizationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('organization', EntityType::class, [
                'class' => Organization::class,
                'choice_label' => 'name',
                'query_builder' => function (OrganizationRepository $repo) {
                    return $repo->createQueryBuilder('o')
                        ->select('o')
                        ->orderBy('o.name', 'ASC');
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
            'data_class' => Organization::class,
            'translation_domain' => 'forms',
        ]);
    }
}
