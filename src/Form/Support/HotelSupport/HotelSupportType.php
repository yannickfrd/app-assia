<?php

namespace App\Form\Support\HotelSupport;

use App\Entity\Evaluation\EvalHousingGroup;
use App\Entity\Support\HotelSupport;
use App\Form\Utils\Choices;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HotelSupportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('reasonNoInclusion', ChoiceType::class, [
                'choices' => Choices::getChoices(HotelSupport::REASON_NO_INCLUSION),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('emergencyActionRequest', ChoiceType::class, [
                'choices' => Choices::getChoices(HotelSupport::EMERGENCY_ACTION_REQUEST),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('emergencyActionDone', ChoiceType::class, [
                'choices' => Choices::getChoices(HotelSupport::EMERGENCY_ACTION_DONE),
                'placeholder' => 'placeholder.select',
                'required' => false,
                ])
            ->add('emergencyActionPrecision')
            ->add('entryHotelDate', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('searchSsd', null, [
                'label' => 'hotelSupport.ssd.search',
                'attr' => [
                    'class' => 'js-search',
                    'placeholder' => 'hotelSupport.ssd.search.placeholder',
                    'autocomplete' => 'off',
                ],
                'help' => null,
                'mapped' => false,
            ])
            ->add('ssd', null, [
                'label' => 'hotelSupport.ssd.city',
                'attr' => [
                    'class' => 'js-city',
                    'readonly' => true,
                ],
            ])
            ->add('evaluationDate', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('levelSupport', ChoiceType::class, [
                'choices' => Choices::getChoices(HotelSupport::LEVEL_SUPPORT),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('agreementDate', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('departmentAnchor', ChoiceType::class, [
                'choices' => Choices::getChoices(Choices::DEPARTMENTS),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('recommendation', ChoiceType::class, [
                'choices' => Choices::getChoices(EvalHousingGroup::SIAO_RECOMMENDATION),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('endSupportReason', ChoiceType::class, [
                'choices' => Choices::getChoices(HotelSupport::END_SUPPORT_REASON),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => HotelSupport::class,
            'translation_domain' => 'forms',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'hotel';
    }
}
