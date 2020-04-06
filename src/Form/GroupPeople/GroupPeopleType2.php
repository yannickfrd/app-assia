<?php

namespace App\Form\GroupPeople;

use App\Entity\GroupPeople;
use App\Form\Utils\Choices;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GroupPeopleType2 extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('familyTypology', ChoiceType::class, [
                'choices' => Choices::getChoices(GroupPeople::FAMILY_TYPOLOGY),
                'placeholder' => '-- Select --',
                'empty_data' => '-- Select --',
            ])
            ->add('nbPeople')
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
