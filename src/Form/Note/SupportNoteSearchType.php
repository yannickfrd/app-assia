<?php

namespace App\Form\Note;

use App\Entity\Note;
use App\Form\Model\SupportNoteSearch;
use App\Form\Utils\Choices;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SupportNoteSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('content', null, [
                'label_attr' => [
                    'class' => 'sr-only',
                ],
                'attr' => [
                    'placeholder' => 'Search',
                ],
            ])
            ->add('type', ChoiceType::class, [
                'label_attr' => [
                    'class' => 'sr-only',
                ],
                'choices' => Choices::getchoices(Note::TYPE),
                'attr' => [
                    'class' => 'w-max-150',
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
                    'class' => 'w-max-150',
                ],
                'placeholder' => '-- Statut --',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SupportNoteSearch::class,
            'translation_domain' => 'forms',
            'allow_extra_fields' => true,
            'csrf_protection' => false,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'search';
    }
}
