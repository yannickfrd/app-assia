<?php

namespace App\Form\Admin;

use App\Entity\Admin\Setting;
use App\Form\Type\LocationType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SettingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('organizationName', TextType::class, [
                'empty_data' => '',
                'required' => false,
            ])
            ->add('location', LocationType::class, [
                'data_class' => Setting::class,
                'attr' => [
                    'geo_location' => true,
                    'location_search_label' => 'setting.location_search',
                ],
            ])
            ->add('softDeletionDelay', IntegerType::class, [
                'help' => 'setting.soft_deletion_delay.help',
                'label' => 'setting.soft_deletion_delay.label',
                'attr' => [
                    'placeholder' => 'setting.soft_deletion_delay.placeholder',
                    'min' => 0,
                ],
                'required' => false,
            ])
            ->add('hardDeletionDelay', IntegerType::class, [
                'help' => 'setting.hard_deletion_delay.help',
                'label' => 'setting.hard_deletion_delay.label',
                'attr' => [
                    'placeholder' => 'setting.hard_deletion_delay.placeholder',
                    'min' => 0,
                ],
                'required' => false,
            ])
            ->add('delayToUpdateSiaoRequest', IntegerType::class, [
                'label' => 'setting.delay_to_update_siao_request',
                'required' => false,
            ])
            ->add('delayToUpdateSocialHousingRequest', IntegerType::class, [
                'label' => 'setting.delay_to_update_social_housing_request',
                'required' => false,
            ])
            ->add('weeklyAlert', CheckboxType::class, [
                'required' => false,
            ])
            ->add('dailyAlert', CheckboxType::class, [
                'required' => false,
            ])
            ->add('endValidPermitDateDelay', IntegerType::class, [
                'label' => 'update_paper',
                'attr' => SettingType::getDelayUpdateAttr(),
                'required' => false,
            ])
            ->add('endRightsSocialSecurityDateDelay', IntegerType::class, [
                'label' => 'update_social_security_rights',
                'attr' => SettingType::getDelayUpdateAttr(),
                'required' => false,
            ])
            ->add('endRqthDateDelay', IntegerType::class, [
                'label' => 'update_rqth',
                'attr' => SettingType::getDelayUpdateAttr(),
                'required' => false,
            ])
            ->add('endRightsDateDelay', IntegerType::class, [
                'label' => 'update_resources_rights',
                'attr' => SettingType::getDelayUpdateAttr(),
                'required' => false,
            ])
            ->add('siaoUpdatedRequestDateDelay', IntegerType::class, [
                'label' => 'update_siao_request',
                'attr' => SettingType::getDelayUpdateAttr(),
                'required' => false,
            ])
            ->add('socialHousingUpdatedRequestDateDelay', IntegerType::class, [
                'label' => 'update_social_housing_request',
                'attr' => SettingType::getDelayUpdateAttr(),
                'required' => false,
            ])
            ->add('endDomiciliationDateDelay', IntegerType::class, [
                'label' => 'update_domiciliation',
                'attr' => SettingType::getDelayUpdateAttr(),
                'required' => false,
            ])
        ;
    }

    public static function getDelayUpdateAttr(): array
    {
        return [
            'placeholder' => 'setting.delay_update.placeholder',
            'min' => 0,
            'max' => 12,
        ];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Setting::class,
            'translation_domain' => 'forms',
        ]);
    }
}
