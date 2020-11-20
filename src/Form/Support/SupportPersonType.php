<?php

namespace App\Form\Support;

use App\Entity\RolePerson;
use App\Entity\SupportGroup;
use App\Entity\SupportPerson;
use App\Form\Utils\Choices;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SupportPersonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('head', CheckBoxType::class, [
                'label' => false,
                'label_attr' => ['class' => 'custom-control-label'],
                'attr' => ['class' => 'custom-control-input checkbox'],
                'help' => 'head.help',
                'help_attr' => ['class' => 'sr-only'],
                'required' => false,
            ])
            ->add('role', ChoiceType::class, [
                'label_attr' => ['class' => 'sr-only'],
                'choices' => Choices::getChoices(RolePerson::ROLE),
                'placeholder' => 'placeholder.select',
                'required' => true,
            ])
            ->add('startDate', DateType::class, [
                'label_attr' => ['class' => 'sr-only'],
                'widget' => 'single_text',
                'attr' => ['class' => 'w-max-165'],
                'required' => true,
            ])
            ->add('endDate', DateType::class, [
                'label_attr' => ['class' => 'sr-only'],
                'widget' => 'single_text',
                'attr' => ['class' => 'w-max-165'],
                'required' => false,
            ])
            ->add('endStatus', ChoiceType::class, [
                'label_attr' => ['class' => 'sr-only'],
                'choices' => Choices::getChoices(SupportGroup::END_STATUS),
                'attr' => ['class' => 'w-min-180'],
                'placeholder' => 'placeholder.select',
                'required' => true,
            ])
            ->add('endStatusComment', null, [
                'attr' => ['class' => 'w-min-160'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SupportPerson::class,
            'translation_domain' => 'forms',
        ]);
    }
}
