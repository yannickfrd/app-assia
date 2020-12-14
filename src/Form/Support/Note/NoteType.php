<?php

namespace App\Form\Support\Note;

use App\Entity\Support\Note;
use App\Form\Utils\Choices;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NoteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', null, [
                'attr' => [
                    'placeholder' => 'Title',
                    'class' => 'font-weight-bold',
                ],
            ])
            ->add('content', null, [
                'attr' => [
                    'class' => 'd-none',
                ],
            ])
            ->add('type', ChoiceType::class, [
                'choices' => Choices::getchoices(Note::TYPE),
                'placeholder' => 'placeholder.type',
            ])
            ->add('status', ChoiceType::class, [
                'choices' => Choices::getchoices(Note::STATUS),
                'placeholder' => 'placeholder.status',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Note::class,
            'translation_domain' => 'forms',
        ]);
    }
}
