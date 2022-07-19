<?php

namespace App\Form\Organization\User;

use App\Entity\Organization\User;
use App\Form\Model\Organization\UserSearch;
use App\Form\Type\ServiceDeviceReferentSearchType;
use App\Form\Utils\Choices;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('lastname', SearchType::class, [
                'attr' => [
                    'class' => 'w-max-140 text-uppercase',
                    'placeholder' => 'Lastname',
                ],
                'required' => false,
            ])
            ->add('firstname', SearchType::class, [
                'attr' => [
                    'class' => 'w-max-140 text-capitalize',
                    'placeholder' => 'Firstname',
                ],
                'required' => false,
            ])
            ->add('status', ChoiceType::class, [
                'choices' => Choices::getChoices(User::STATUS),
                'multiple' => true,
                'attr' => [
                    'class' => 'multi-select',
                    'placeholder' => 'placeholder.status',
                    'size' => 1,
                ],
                'required' => false,
            ])
            ->add('phone', null, [
                'attr' => [
                    'placeholder' => 'Phone',
                    'class' => 'w-max-140',
                    'data-phone' => 'true',
                    'autocomplete' => 'off',
                ],
            ])
            ->add('service', ServiceDeviceReferentSearchType::class, [
                'attr' => [
                    'options' => ['poles', 'services'],
                ],
            ])
            ->add('disabled', ChoiceType::class, [
                'choices' => Choices::getChoices(Choices::DISABLE),
                'placeholder' => 'placeholder.disabled',
                'required' => false,
            ])
            ->add('export');
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UserSearch::class,
            'method' => 'get',
            'translation_domain' => 'forms',
            'allow_extra_fields' => true,
            'csrf_protection' => false,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}
