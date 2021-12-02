<?php

namespace App\Form\People\Person;

use App\Entity\People\RolePerson;
use App\Form\People\PeopleGroup\PeopleGroupType;
use App\Form\Utils\Choices;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RolePersonGroupType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('role', ChoiceType::class, [
                'choices' => Choices::getChoices(RolePerson::ROLE),
                'placeholder' => 'placeholder.select',
                'required' => true,
            ])
            ->add('person', PersonType::class)
            ->add('peopleGroup', PeopleGroupType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RolePerson::class,
            'translation_domain' => 'forms',
        ]);
    }
}
