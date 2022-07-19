<?php

namespace App\Form\Organization\Service;

use App\Entity\Organization\Pole;
use App\Form\Model\Organization\ServiceSearch;
use App\Form\Utils\Choices;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ServiceSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', SearchType::class, [
                'attr' => [
                    'class' => 'w-max-200',
                    'placeholder' => 'Service name',
                    'autocomplete' => 'off',
                ],
                'required' => false,
            ])
            ->add('city', SearchType::class, [
                'attr' => [
                    'class' => 'w-max-160',
                    'placeholder' => 'City',
                    'autocomplete' => 'off',
                ],
                'required' => false,
            ])
            ->add('phone', null, [
                'attr' => [
                    'placeholder' => 'Phone',
                    'class' => 'w-max-140',
                    'data-phone' => 'true',
                    'autocomplete' => 'off',
                ],
            ])
            ->add('pole', EntityType::class, [
                'class' => Pole::class,
                'choice_label' => 'name',
                'placeholder' => 'placeholder.pole',
                'required' => false,
            ])
            ->add('disabled', ChoiceType::class, [
                'choices' => Choices::getChoices(Choices::DISABLE),
                'placeholder' => 'placeholder.disabled',
                'required' => false,
            ])
            ->add('export');
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ServiceSearch::class,
            'method' => 'get',
            'translation_domain' => 'forms',
            'allow_extra_fields' => true,
            'csrf_protection' => false,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}
