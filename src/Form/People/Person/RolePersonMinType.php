<?php

namespace App\Form\People\Person;

use App\Entity\People\RolePerson;
use App\Form\Utils\Choices;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\RadioType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RolePersonMinType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('head', RadioType::class, [
                'label' => false,
                'help' => 'head.help',
                'required' => false,
            ])
            ->add('role', ChoiceType::class, [
                'choices' => Choices::getChoices(RolePerson::ROLE),
                'placeholder' => 'placeholder.select',
                'required' => true,
            ])
            ->add('person', PersonMinType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RolePerson::class,
            'translation_domain' => 'forms',
        ]);
    }
}
