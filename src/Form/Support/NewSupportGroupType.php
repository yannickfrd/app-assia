<?php

namespace App\Form\Support;

use App\Entity\Service;
use App\Entity\SubService;
use App\Repository\ServiceRepository;
use App\Repository\SubServiceRepository;
use App\Security\CurrentUserService;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NewSupportGroupType extends AbstractType
{
    private $currentUser;

    public function __construct(CurrentUserService $currentUser)
    {
        $this->currentUser = $currentUser;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('service', EntityType::class, [
                'class' => Service::class,
                'choice_label' => 'name',
                'query_builder' => function (ServiceRepository $repo) {
                    return $repo->getServicesFromUserQueryList($this->currentUser);
                },
                'placeholder' => 'placeholder.select',
            ])
            // ->add('subService', EntityType::class, [
            //     'class' => SubService::class,
            //     'choice_label' => 'name',
            //     'query_builder' => function (SubServiceRepository $repo) {
            //         return $repo->getSubServicesFromUserQueryList($this->currentUser);
            //     },
            //     'placeholder' => 'placeholder.select',
            // ])
            ->add('referent', HiddenType::class)
            ->add('status', HiddenType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'forms',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'support';
    }
}
