<?php

namespace App\Form\Organization\Service;

use App\Entity\Admin\Setting;
use App\Entity\Organization\ServiceSetting;
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
                'help' => 'setting.soft_deletion_delay.service_help',
                'attr' => [
                    'placeholder' => 'setting.soft_deletion_delay.placeholder',
                    'min' => $this->minimumDeletionDelay['soft'],
                ],
                'required' => false,
            ])
            ->add('hardDeletionDelay', IntegerType::class, [
                'constraints' => [
                    new Assert\Callback([
                        'callback' => function (?int $value, ExecutionContextInterface $context) {
                            if ($value < $this->minimumDeletionDelay['soft']) {
                                $context->buildViolation(
                                    'La valeur du champ, ne peut pas être inférieur à '
                                    .$this->minimumDeletionDelay['hard']
                                )
                                ->addViolation();
                            }
                        },
                    ]),
                ],
                'help' => 'setting.hard_deletion_delay.service_help',
                'attr' => [
                    'placeholder' => 'setting.hard_deletion_delay.placeholder',
                    'min' => $this->minimumDeletionDelay['hard'],
                ],
                'required' => false,
            ])
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
