<?php

namespace App\Form\Support\Document;

use App\Form\Utils\Choices;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ActionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', ChoiceType::class, [
                // 'label' => 'document.action',
                'label_attr' => ['class' => 'sr-only'],
                'choices' => Choices::getchoices([
                    1 => 'Télécharger',
                    2 => 'Supprimer',
                ]),
                'placeholder' => '-- Action --',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'allow_extra_fields' => true,
            'translation_domain' => 'forms',
        ]);
    }
}
