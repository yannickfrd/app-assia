<?php

namespace App\Form\Organization\Tag;

use App\Entity\Organization\Tag;
use App\Form\Utils\Choices;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TagType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', null, [
                'attr' => ['placeholder' => 'tag.placeholder'],
            ])
            ->add('code', IntegerType::class, [
                'required' => false,
            ])
            ->add('color', ChoiceType::class, [
                'choices' => Choices::getChoices(Tag::COLORS),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('categories', ChoiceType::class, [
                'multiple' => true,
                'choices' => Choices::getChoices(Tag::CATEGORIES),
                'attr' => [
                    'placeholder' => 'placeholder.categories',
                    'size' => 1,
                ],
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Tag::class,
            'translation_domain' => 'forms',
        ]);
    }
}
