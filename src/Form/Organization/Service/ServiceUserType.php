<?php

namespace App\Form\Organization\Service;

use App\Entity\Organization\Service;
use App\Entity\Organization\ServiceUser;
use App\Entity\Organization\User;
use App\Repository\Organization\ServiceRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;

class ServiceUserType extends AbstractType
{
    /** @var User */
    private $user;

    public function __construct(Security $security)
    {
        $this->user = $security->getUser();
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('service', EntityType::class, [
                'class' => Service::class,
                'choice_label' => 'name',
                'query_builder' => function (ServiceRepository $repo) {
                    return $repo->getServicesOfUserQueryBuilder($this->user);
                },
                'placeholder' => 'placeholder.select',
                'attr' => [
                    'class' => 'col-auto my-1 w-min-200',
                ],
            ])
            ->add('main', CheckboxType::class, [
                'label' => false,
                'required' => false,
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
