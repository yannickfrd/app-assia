<?php

namespace App\Form\Admin;

use App\Entity\Organization\Pole;
use App\Form\Model\Admin\OccupancySearch;
use App\Form\Model\Support\RdvSearch;
use App\Form\Type\DateSearchType;
use App\Form\Utils\Choices;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OccupancySearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('year', ChoiceType::class, [
                'label_attr' => ['class' => 'pr-1'],
                'choices' => Choices::getYears(5),
                'placeholder' => 'Year',
                'required' => false,
            ])
            ->add('pole', EntityType::class, [
                'class' => Pole::class,
                'choice_label' => 'name',
                'label_attr' => [
                    'class' => 'sr-only',
                ],
                'placeholder' => 'placeholder.pole',
                'required' => false,
            ])
            ->add('date', DateSearchType::class, [
                'data_class' => RdvSearch::class,
            ])
            ->add('export');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => OccupancySearch::class,
            'method' => 'get',
            'translation_domain' => 'forms',
            'csrf_protection' => false,
            'allow_extra_fields' => true,
        ]);
    }

    public function getBlockPrefix()
    {
        return '';
    }
}
