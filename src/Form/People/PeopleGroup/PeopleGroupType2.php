<?php

namespace App\Form\People\PeopleGroup;

use App\Entity\People\PeopleGroup;
use App\Form\Utils\Choices;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PeopleGroupType2 extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('familyTypology', ChoiceType::class, [
                'choices' => Choices::getChoices(PeopleGroup::FAMILY_TYPOLOGY),
                'placeholder' => 'placeholder.select',
                'empty_data' => 'placeholder.select',
            ])
            ->add('nbPeople')
            ->add('siSiaoId', null, [
                    'attr' => ['data-mask-type' => 'number'],
            ])
            ->add('comment', null, [
                'attr' => [
                    'rows' => 4,
                    'placeholder' => 'peopleGroup.comment',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PeopleGroup::class,
            'translation_domain' => 'forms',
        ]);
    }
}
