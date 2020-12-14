<?php

namespace App\Form\Organization\User;

use App\Form\Utils\Choices;
use App\Form\Type\SearchType;
use App\Entity\Organization\Pole;
use App\Entity\Organization\User;
use App\Entity\Organization\ServiceUser;
use Symfony\Component\Form\AbstractType;
use App\Form\Model\Organization\UserSearch;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class UserSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('lastname', null, [
                'label_attr' => [
                    'class' => 'sr-only',
                ],
                'attr' => [
                    'class' => 'w-max-140 text-uppercase',
                    'placeholder' => 'Lastname',
                ],
            ])
            ->add('firstname', null, [
                'label_attr' => [
                    'class' => 'sr-only',
                ],
                'attr' => [
                    'class' => 'w-max-140 text-capitalize',
                    'placeholder' => 'Firstname',
                ],
            ])
            ->add('status', ChoiceType::class, [
                'choices' => Choices::getChoices(User::STATUS),
                'label_attr' => [
                    'class' => 'sr-only',
                ],
                'placeholder' => '-- Fonction --',
                'required' => false,
            ])
            ->add('serviceUser', ChoiceType::class, [
                'choices' => Choices::getChoices(ServiceUser::ROLE),
                'label_attr' => [
                    'class' => 'sr-only',
                ],
                'attr' => [
                    'class' => 'w-max-120',
                ],
                'placeholder' => '-- Rôle --',
                'required' => false,
            ])
            ->add('phone', null, [
                'label_attr' => [
                    'class' => 'sr-only',
                ],
                'attr' => [
                    'placeholder' => 'Phone',
                    'class' => 'js-phone w-max-140',
                    'autocomplete' => 'off',
                ],
            ])
            ->add('service', SearchType::class, [
                'attr' => [
                    'options' => ['services'],
                ],
            ])
            ->add('pole', EntityType::class, [
                'class' => Pole::class,
                'choice_label' => 'name',
                // "multiple" => true,
                'label_attr' => [
                    'class' => 'sr-only',
                ],
                'placeholder' => 'placeholder.pole',
                'required' => false,
                ])
            ->add('disabled', ChoiceType::class, [
                'label_attr' => ['class' => 'sr-only'],
                'choices' => Choices::getChoices(Choices::DISABLE),
                'placeholder' => 'placeholder.disabled',
                'required' => false,
            ])
            ->add('export');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => UserSearch::class,
            'method' => 'get',
            'translation_domain' => 'forms',
            'allow_extra_fields' => true,
            'csrf_protection' => false,
        ]);
    }

    public function getBlockPrefix()
    {
        return '';
    }
}
