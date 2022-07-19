<?php

namespace App\Form\Organization\Service;

use App\Entity\Admin\Setting;
use App\Entity\Organization\ServiceSetting;
use App\Form\Admin\SettingType;
use Doctrine\Common\Annotations\Annotation\Required;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class ServiceSettingType extends AbstractType
{
    /** @var EntityManagerInterface */
    private $em;
    private $minimumDeletionDelay;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /** @Required */
    public function setMinimumDeletionDelay()
    {
        $previous = $this->minimumDeletionDelay;

        /** @var Setting $setting */
        $setting = $this->em->getRepository(Setting::class)->findOneBy([]) ?? new Setting();
        $this->minimumDeletionDelay['soft'] = $setting->getSoftDeletionDelay() ?? Setting::DEFAULT_SOFT_DELETION_DELAY;
        $this->minimumDeletionDelay['hard'] = $setting->getHardDeletionDelay() ?? Setting::DEFAULT_HARD_DELETION_DELAY;

        return $previous;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('softDeletionDelay', IntegerType::class, [
                'constraints' => [
                    new Assert\Callback([
                        'callback' => function (?int $value, ExecutionContextInterface $context) {
                            if ($value < $this->minimumDeletionDelay['soft']) {
                                $context->buildViolation(
                                    'La valeur du champ, ne peut pas être inférieur à '
                                    .$this->minimumDeletionDelay['soft']
                                )
                                ->addViolation();
                            }
                        },
                    ]),
                ],
                'help' => 'setting.soft_deletion_delay.help',
                'attr' => [
                    'placeholder' => 'setting.soft_deletion_delay.placeholder',
                    'min' => $this->minimumDeletionDelay['soft'],
                ],
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

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ServiceSetting::class,
            'translation_domain' => 'forms',
        ]);
    }
}
