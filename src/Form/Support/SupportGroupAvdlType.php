<?php

namespace App\Form\Support;

use App\Form\Utils\Choices;
use App\Entity\SupportGroup;
use App\Entity\Accommodation;
use App\Form\Type\LocationType;
use Symfony\Component\Form\AbstractType;
use App\Form\OriginRequest\OriginRequestType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class SupportGroupAvdlType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('originRequest', OriginRequestType::class)
            ->add('startDate', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('endDate', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('endStatus', ChoiceType::class, [
                'choices' => Choices::getChoices(SupportGroup::END_STATUS),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('endStatusComment')
            ->add('location', LocationType::class, [
                'data_class' => Accommodation::class,
                'data' => [
                    'seachLabel' => 'Adresse du suivi',
                    'seachHelp' => 'Adresse du logement, hébergement, domiciliation...',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SupportGroup::class,
            'translation_domain' => 'forms',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'support';
    }
}
