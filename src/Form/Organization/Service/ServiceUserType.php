<?php

namespace App\Form\Organization\Service;

use App\Entity\Organization\Service;
use App\Entity\Organization\ServiceUser;
use App\Repository\Organization\ServiceRepository;
use App\Security\CurrentUserService;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ServiceUserType extends AbstractType
{
    private $currentUser;

    public function __construct(CurrentUserService $currentUser)
    {
        $this->currentUser = $currentUser;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('service', EntityType::class, [
                'class' => Service::class,
                'choice_label' => 'name',
                'query_builder' => function (ServiceRepository $repo) {
//                    return $repo->findAllQuery();
                    return $repo->getServicesOfUserQueryBuilder($this->currentUser);
                },
                'placeholder' => 'placeholder.select',
                'attr' => [
                    'class' => 'col-auto my-1 w-min-200',
                ],
//                'mapped' => false,
            ])
            ->add('main', CheckboxType::class, [
                'label' => false,
                'label_attr' => [
                    'class' => 'custom-control-label',
                ],
                'attr' => [
                    'class' => 'custom-control-input checkbox',
                ],
                'required' => false,
//                'mapped' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ServiceUser::class,
            'translation_domain' => 'forms',
        ]);
    }
}
