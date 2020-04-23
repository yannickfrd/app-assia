<?php

namespace App\Form\GroupPeople;

use App\Entity\GroupPeople;
use App\Form\Person\RolePersonMinType;
use App\Form\Utils\Choices;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GroupPeopleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('familyTypology', ChoiceType::class, [
                'choices' => Choices::getChoices(GroupPeople::FAMILY_TYPOLOGY),
                'placeholder' => '-- Select --',
            ])
            ->add('nbPeople')
            ->add('rolePeople', CollectionType::class, [
                'entry_type' => RolePersonMinType::class,
                'allow_add' => false,
                'allow_delete' => false,
                'required' => true,
            ])
            ->add('comment', null, [
                'attr' => [
                    'rows' => 4,
                    'placeholder' => 'Write a comment about the group',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => GroupPeople::class,
            'translation_domain' => 'forms',
        ]);
    }
}
