<?php

namespace App\Form\Note;

use App\Entity\Note;
use App\Form\Utils\Choices;
use App\Form\Model\NoteSearch;
use App\Form\Type\DateSearchType;
use App\Form\Type\ServiceSearchType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class NoteSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('content', null, [
                'label_attr' => ['class' => 'sr-only'],
                'attr' => [
                    'placeholder' => 'Search',
                    'class' => 'w-max-170',
                ],
            ])
            ->add('fullname', null, [
                'label_attr' => ['class' => 'sr-only'],
                'attr' => [
                    'placeholder' => 'search.fullname.placeholder',
                    'class' => 'w-max-170',
                ],
            ])
            ->add('type', ChoiceType::class, [
                'label_attr' => [
                    'class' => 'sr-only',
                ],
                'choices' => Choices::getchoices(Note::TYPE),
                'attr' => [
                    'class' => 'w-max-160',
                ],
                'placeholder' => '-- Type --',
                'required' => false,
            ])
            ->add('status', ChoiceType::class, [
                'label_attr' => [
                    'class' => 'sr-only',
                ],
                'choices' => Choices::getchoices(Note::STATUS),
                'attr' => [
                    'class' => 'w-max-160',
                ],
                'placeholder' => '-- Statut --',
                'required' => false,
            ])
            ->add('date', DateSearchType::class, [
                'data_class' => NoteSearch::class,
            ])
            ->add('service', ServiceSearchType::class, [
                'data_class' => NoteSearch::class,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => NoteSearch::class,
            'method' => 'get',
            'translation_domain' => 'forms',
            'allow_extra_fields' => true,
            'csrf_protection' => false,
        ]);
    }

    public function getBlockPrefix()
    {
        return '';
    }
}
