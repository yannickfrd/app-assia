<?php

namespace App\Form\Pole;

use App\Entity\Pole;
use App\Entity\User;
use App\Form\Utils\Choices;
use App\Form\Type\LocationType;
use App\Repository\UserRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class PoleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('email')
            ->add('phone1', null, [
                'attr' => [
                    'class' => 'js-phone',
                ],
            ])
            ->add('chief', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'fullname',
                'query_builder' => function (UserRepository $repo) {
                    return $repo->createQueryBuilder('u')
                        ->where('u.status = 4')
                        ->andWhere('u.disabledAt IS NULL')
                        ->orderBy('u.lastname', 'ASC');
                },
                'placeholder' => '-- Select --',
                'required' => false,
            ])
            ->add('comment')
            ->add('color', ChoiceType::class, [
                'choices' => Choices::getChoices(Pole::COLOR),
                'placeholder' => '-- Select --',
            ])
            ->add('location', LocationType::class, [
                'data_class' => Pole::class,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Pole::class,
            'translation_domain' => 'forms',
        ]);
    }
}
