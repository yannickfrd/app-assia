<?php

namespace App\Form\People\PeopleGroup;

use App\Entity\People\PeopleGroup;
use App\Form\People\Person\RolePersonMinType;
use App\Form\Utils\Choices;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PeopleGroupType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('familyTypology', ChoiceType::class, [
                'choices' => Choices::getChoices(PeopleGroup::FAMILY_TYPOLOGY),
                'placeholder' => 'placeholder.select',
            ])
            ->add('nbPeople')
            ->add('siSiaoId', null, [
                'attr' => [
                    'data-mask-type' => 'number',
                ],
            ])
            ->add('rolePeople', CollectionType::class, [
                'entry_type' => RolePersonMinType::class,
                'allow_add' => false,
                'allow_delete' => false,
                'required' => true,
            ])
            ->add('comment', null, [
                'attr' => [
                    'rows' => 4,
                    'placeholder' => 'peopleGroup.comment',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PeopleGroup::class,
            'translation_domain' => 'forms',
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'group';
    }
}
