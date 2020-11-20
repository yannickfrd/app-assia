<?php

namespace App\Form\Device;

use App\Entity\Device;
use App\Form\Utils\Choices;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DeviceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('coefficient', null, [
                'help' => 'device.coefficient.help',
            ])
            ->add('accommodation', ChoiceType::class, [
                'choices' => Choices::getChoices(Choices::YES_NO),
                'placeholder' => 'placeholder.select',
                'required' => false,
                'help' => 'device.accommodation.help',
            ])

            ->add('justice', ChoiceType::class, [
                'choices' => Choices::getChoices(Choices::YES_NO),
                'label' => 'Justice activity',
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('preAdmission', ChoiceType::class, [
                'choices' => Choices::getChoices(Choices::YES_NO),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('contribution', ChoiceType::class, [
                'choices' => Choices::getChoices(Choices::YES_NO),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('contributionType', ChoiceType::class, [
                'choices' => Choices::getChoices(Device::CONTRIBUTION_TYPE),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('contributionRate', null, [
                'help' => 'contribution.rate.help',
            ])
            ->add('comment', null, [
                'attr' => [
                    'rows' => 5,
                    'placeholder' => 'service.comment.placeholder',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Device::class,
            'translation_domain' => 'forms',
        ]);
    }
}
