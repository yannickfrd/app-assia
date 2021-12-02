<?php

namespace App\Form\Organization\Pole;

use App\Entity\Organization\Organization;
use App\Entity\Organization\Pole;
use App\Entity\Organization\User;
use App\Form\Type\LocationType;
use App\Form\Utils\Choices;
use App\Repository\Organization\OrganizationRepository;
use App\Repository\Organization\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PoleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('email')
            ->add('phone1', null, [
                'attr' => ['class' => 'js-phone'],
            ])
            ->add('chief', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'fullname',
                'query_builder' => function (UserRepository $repo) {
                    return $repo->createQueryBuilder('u')
                        ->where('u.status = '.User::STATUS_DIRECTOR)
                        ->andWhere('u.disabledAt IS NULL')
                        ->orderBy('u.lastname', 'ASC');
                },
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('organization', EntityType::class, [
                'class' => Organization::class,
                'choice_label' => 'name',
                'query_builder' => function (OrganizationRepository $repo) {
                    return $repo->createQueryBuilder('o')
                        ->orderBy('o.name', 'ASC');
                },
                'label' => 'organization.name',
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('logoPath')
            ->add('comment')
            ->add('color', ChoiceType::class, [
                'choices' => Choices::getChoices(Pole::COLOR),
                'placeholder' => 'placeholder.select',
            ])
            ->add('location', LocationType::class, [
                'data_class' => Pole::class,
                'attr' => ['seachLabel' => 'Adresse du pôle'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Pole::class,
            'translation_domain' => 'forms',
        ]);
    }
}
