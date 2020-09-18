<?php

namespace App\Form\HotelSupport;

use App\Entity\EvalHousingGroup;
use App\Entity\HotelSupport;
use App\Form\Utils\Choices;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HotelSupportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('entryHotelDate', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('originDept', ChoiceType::class, [
                'choices' => Choices::getChoices(HotelSupport::ORIGIN_DEPT),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('gipId')
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
            ->add('diagStartDate', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('diagEndDate', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
            ])
            // ->add('endStatusDiag', ChoiceType::class, [
            //     'choices' => Choices::getChoices(HotelSupport::END_STATUS_DIAG),
            //     'placeholder' => 'placeholder.select',
            //     'required' => false,
            // ])
            ->add('diagComment', TextareaType::class, [
                'attr' => [
                    'rows' => 2,
                    'placeholder' => 'avdl.diagComment.placeholder',
                ],
                'required' => false,
            ])
            ->add('supportStartDate', DateType::class, [
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
                'choices' => Choices::getChoices(Choices::YES_NO),
                'placeholder' => 'placeholder.select',
                'required' => false,
                ])
            ->add('recommendation', ChoiceType::class, [
                'choices' => Choices::getChoices(EvalHousingGroup::SIAO_RECOMMENDATION),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('supportEndDate', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('supportComment', TextareaType::class, [
                'attr' => [
                    'rows' => 2,
                    'placeholder' => 'avdl.supportComment.placeholder',
                ],
                'required' => false,
            ])
            ->add('endSupportReason', ChoiceType::class, [
                'choices' => Choices::getChoices(HotelSupport::END_SUPPORT_REASON),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('endSupportComment', TextareaType::class, [
                'attr' => [
                    'rows' => 2,
                    'placeholder' => 'avdl.endSupportComment.placeholder',
                ],
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
