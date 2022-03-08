<?php

namespace App\Form\Organization\User;

use App\Entity\Organization\UserSetting;
use App\Form\Admin\SettingType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserSettingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('weeklyAlert', CheckboxType::class, [
                'label_attr' => ['class' => 'custom-control-label'],
                'attr' => ['class' => 'custom-control-input checkbox'],
                'required' => false,
            ])
            ->add('dailyAlert', CheckboxType::class, [
                'label_attr' => ['class' => 'custom-control-label'],
                'attr' => ['class' => 'custom-control-input checkbox'],
                'required' => false,
            ])
            ->add('autoEvaluationAlerts', CheckboxType::class, [
                'label' => 'setting.auto_evaluation_alerts.label',
                'label_attr' => ['class' => 'custom-control-label'],
                'attr' => ['class' => 'custom-control-input checkbox'],
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
                'help' => 'setting.update_siao_request.help',
                'required' => false,
            ])
            ->add('socialHousingUpdatedRequestDateDelay', IntegerType::class, [
                'label' => 'update_social_housing_request',
                'attr' => SettingType::getDelayUpdateAttr(),
                'help' => 'setting.update_social_housing_request.help',
                'required' => false,
            ])
            ->add('endDomiciliationDateDelay', IntegerType::class, [
                'label' => 'update_domiciliation',
                'attr' => SettingType::getDelayUpdateAttr(),
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UserSetting::class,
            'translation_domain' => 'forms',
        ]);
    }
}
