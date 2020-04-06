<?php

namespace App\Form\Pole;

use App\Entity\Pole;
use App\Entity\User;
use App\Form\Utils\Choices;
use App\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PoleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('email')
            ->add('phone', null, [
                'attr' => [
                    'class' => 'js-phone',
                ],
            ])
            ->add('address')
            ->add('city')
            ->add('zipCode', null, [
                'attr' => [
                    'class' => 'js-zip-code ',
                ],
            ])
            ->add('chief', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'fullname',
                'query_builder' => function (UserRepository $repo) {
                    return $repo->createQueryBuilder('u')
                        ->where('u.status = 4')
                        ->andWhere('u.enabled = TRUE')
                        ->orderBy('u.lastname', 'ASC');
                },
                'placeholder' => '-- Select --',
                'required' => false,
            ])
            ->add('comment')
            ->add('color', ChoiceType::class, [
                'choices' => Choices::getChoices(Pole::COLOR),
                'placeholder' => '-- Select --',
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
