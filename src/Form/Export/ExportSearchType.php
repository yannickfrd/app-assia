<?php

namespace App\Form\Export;

use App\Entity\Device;
use App\Entity\Service;
use App\Entity\SubService;
use App\Entity\SupportGroup;
use App\Entity\User;
use App\Form\Model\ExportSearch;
use App\Form\Model\SupportSearch;
use App\Form\Utils\Choices;
use App\Repository\DeviceRepository;
use App\Repository\ServiceRepository;
use App\Repository\SubServiceRepository;
use App\Repository\UserRepository;
use App\Security\CurrentUserService;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExportSearchType extends AbstractType
{
    private $currentUser;

    public function __construct(CurrentUserService $currentUser)
    {
        $this->currentUser = $currentUser;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('status', ChoiceType::class, [
                'multiple' => true,
                'choices' => Choices::getChoices(SupportGroup::STATUS),
                'attr' => [
                    'class' => 'multi-select',
                    'data-select2-id' => 'status',
                ],
                'placeholder' => 'placeholder.status',
                'required' => false,
            ])
            ->add('supportDates', ChoiceType::class, [
                'choices' => Choices::getChoices(SupportSearch::SUPPORT_DATES),
                'attr' => [
                    'class' => '',
                ],
                'placeholder' => '-- Date de suivi --',
                'required' => false,
            ])
            ->add('start', DateType::class, [
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'w-max-165',
                ],
                'required' => false,
            ])
            ->add('end', DateType::class, [
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'w-max-165',
                ],
                'required' => false,
            ])
            ->add('referents', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'fullname',
                'multiple' => true,
                'query_builder' => function (UserRepository $repo) {
                    return $repo->getAllUsersFromServicesQueryList($this->currentUser);
                },
                'placeholder' => '-- Référent --',
                'attr' => [
                    'class' => 'multi-select',
                    'data-select2-id' => 'referents',
                ],
                'required' => false,
            ])
            ->add('services', EntityType::class, [
                'class' => Service::class,
                'choice_label' => 'name',
                'multiple' => true,
                'query_builder' => function (ServiceRepository $repo) {
                    return $repo->getServicesFromUserQueryList($this->currentUser);
                },
                'attr' => [
                    'class' => 'multi-select',
                    'data-select2-id' => 'services',
                ],
                'required' => false,
            ])
            ->add('subServices', EntityType::class, [
                'class' => SubService::class,
                'choice_label' => 'name',
                'multiple' => true,
                'query_builder' => function (SubServiceRepository $repo) {
                    return $repo->getSubServicesFromUserQueryList($this->currentUser);
                },
                'attr' => [
                    'class' => 'multi-select',
                    'data-select2-id' => 'sub-services',
                ],
                'required' => false,
            ])
            ->add('devices', EntityType::class, [
                'class' => Device::class,
                'choice_label' => 'name',
                'multiple' => true,
                'query_builder' => function (DeviceRepository $repo) {
                    return $repo->getDevicesFromUserQueryList($this->currentUser);
                },
                'attr' => [
                    'class' => 'multi-select',
                    'data-select2-id' => 'devices',
                ],
                'required' => false,
            ])
            // ->add('evalSocial', CheckBoxType::class, [
            //     'required' => false,
            //     'label_attr' => [
            //         'class' => 'custom-control-label',
            //     ],
            //     'attr' => [
            //         'class' => 'custom-control-input checkbox',
            //     ],
            // ])
            // ->add('evalAdm', CheckBoxType::class, [
            //     'required' => false,
            //     'label_attr' => [
            //         'class' => 'custom-control-label',
            //     ],
            //     'attr' => [
            //         'class' => 'custom-control-input checkbox',
            //     ],
            // ])
            // ->add('evalFamily', CheckBoxType::class, [
            //     'required' => false,
            //     'label_attr' => [
            //         'class' => 'custom-control-label',
            //     ],
            //     'attr' => [
            //         'class' => 'custom-control-input checkbox',
            //     ],
            // ])
            // ->add('evalBudget', CheckBoxType::class, [
            //     'required' => false,
            //     'label_attr' => [
            //         'class' => 'custom-control-label',
            //     ],
            //     'attr' => [
            //         'class' => 'custom-control-input checkbox',
            //     ],
            // ])
            // ->add('evalProf', CheckBoxType::class, [
            //     'required' => false,
            //     'label_attr' => [
            //         'class' => 'custom-control-label',
            //     ],
            //     'attr' => [
            //         'class' => 'custom-control-input checkbox',
            //     ],
            // ])
            // ->add('evalHousing', CheckBoxType::class, [
            //     'required' => false,
            //     'label_attr' => [
            //         'class' => 'custom-control-label',
            //     ],
            //     'attr' => [
            //         'class' => 'custom-control-input checkbox',
            //     ],
            // ])
            // ->add('evalJustice', CheckBoxType::class, [
            //     'required' => false,
            //     'label_attr' => [
            //         'class' => 'custom-control-label',
            //     ],
            //     'attr' => [
            //         'class' => 'custom-control-input checkbox',
            //     ],
            // ])
            ->add('calcul', null, [
                'mapped' => false,
            ])
            ->add('export', null, [
                'mapped' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ExportSearch::class,
            'csrf_protection' => false,
            'translation_domain' => 'forms',
        ]);
    }

    public function getBlockPrefix()
    {
        return '';
    }
}
