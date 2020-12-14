<?php

namespace App\Form\Support\Document;

use App\Entity\Support\Document;
use App\Form\Model\Support\DocumentSearch;
use App\Form\Type\DateSearchType;
use App\Form\Type\SearchType;
use App\Form\Utils\Choices;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DocumentSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', null, [
                'attr' => [
                    'placeholder' => 'ID',
                    'class' => 'w-max-80',
                ],
            ])
            ->add('name', null, [
                'attr' => ['placeholder' => 'Search'],
            ])
            ->add('type', ChoiceType::class, [
                'label_attr' => ['class' => 'sr-only'],
                'choices' => Choices::getchoices(Document::TYPE),
                'attr' => ['class' => 'w-max-150'],
                'placeholder' => 'placeholder.type',
                'required' => false,
            ])
            ->add('date', DateSearchType::class, [
                'data_class' => DocumentSearch::class,
            ])
            ->add('service', SearchType::class, [
                'data_class' => DocumentSearch::class,
            ]);
        // ->add('export');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => DocumentSearch::class,
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
