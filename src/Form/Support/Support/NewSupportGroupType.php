<?php

namespace App\Form\Support\Support;

use App\Entity\Organization\Device;
use App\Entity\Organization\Place;
use App\Entity\Organization\Service;
use App\Entity\Organization\SubService;
use App\Entity\Organization\User;
use App\Entity\Support\SupportGroup;
use App\Repository\Organization\DeviceRepository;
use App\Repository\Organization\PlaceRepository;
use App\Repository\Organization\ServiceRepository;
use App\Repository\Organization\UserRepository;
use App\Security\CurrentUserService;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NewSupportGroupType extends AbstractType
{
    private $currentUserService;

    public function __construct(CurrentUserService $currentUserService)
    {
        $this->currentUserService = $currentUserService;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $this->setFormData($builder);

        $builder
            ->add('service', EntityType::class, [
                'class' => Service::class,
                'choice_label' => 'name',
                'query_builder' => function (ServiceRepository $repo) {
                    return $repo->getServicesOfUserQueryBuilder($this->currentUserService);
                },
                'placeholder' => 'placeholder.select',
            ])
            ->add('subService', ChoiceType::class)
            ->add('device', ChoiceType::class, [
                'choice_value' => 'code',
                'placeholder' => 'placeholder.select',
            ])
            ->add('cloneSupport', CheckboxType::class, [
                'label_attr' => ['class' => 'custom-control-label'],
                'attr' => ['class' => 'custom-control-input checkbox'],
                'required' => false,
                'mapped' => false,
            ])
            ->add('siSiaoImport', CheckboxType::class, [
                'label_attr' => ['class' => 'custom-control-label'],
                'attr' => ['class' => 'custom-control-input checkbox'],
                'required' => false,
                'mapped' => false,
            ])
            ->add('status', HiddenType::class)
            ->add('referent', HiddenType::class);

        $builder->get('service')->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();
            $service = $form->getData();

            $this->addFieldsAfterSubmitService($form->getParent(), $service);

            $builder = $this->getSubServiceBuilder($form->getParent(), $service);
            $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use ($service) {
                $this->addPlaceField($event->getForm(), $service);
            });

            $form->getParent()->add($builder->getForm());
        });
    }

    protected function setFormData(FormBuilderInterface $builder): FormBuilderInterface
    {
        return $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            /** @var SupportGroup $supportGroup */
            $supportGroup = $event->getData();
            $supportGroup->setReferent($this->currentUserService->getUser());
        });
    }

    protected function addFieldsAfterSubmitService(FormInterface $form, Service $service): void
    {
        $optionsReferent = $this->optionsReferent($service);

        $form
            ->add('subService', EntityType::class, [
                'class' => SubService::class,
                'choices' => $service->getSubServices(),
                'choice_label' => 'name',
                'placeholder' => 'placeholder.select',
            ])
            ->add('device', EntityType::class, [
                'class' => Device::class,
                'choice_label' => 'name',
                'choice_value' => 'code',
                'query_builder' => function (DeviceRepository $repo) use ($service) {
                    return $repo->getDevicesOfUserQueryBuilder($this->currentUserService, $service);
                },
                'placeholder' => 'placeholder.select',
            ])
            ->add('referent', EntityType::class, $optionsReferent)
            ->add('referent2', EntityType::class, $optionsReferent);
    }

    protected function addPlaceField(FormInterface $form, Service $service): void
    {
        $subService = $form->getData();

        $form->getParent()
            ->add('place', EntityType::class, [
                'class' => Place::class,
                'choice_label' => 'name',
                'query_builder' => function (PlaceRepository $repo) use ($service, $subService) {
                    return $repo->getPlacesQueryBuilder($service, $subService);
                },
                'placeholder' => 'placeholder.select',
                'mapped' => false,
                'required' => false,
            ]);
    }

    protected function getSubServiceBuilder(FormInterface $form, Service $service): FormBuilderInterface
    {
        return $form->getConfig()->getFormFactory()->createNamedBuilder(
            'subService',
            EntityType::class,
            null, [
                'class' => SubService::class,
                'choices' => $service->getSubServices(),
                'auto_initialize' => false,
                'choice_label' => 'name',
                'placeholder' => 'placeholder.select',
                'required' => false,
        ]);
    }

    /**
     * Retourne les options du champ Référent.
     */
    protected function optionsReferent(?Service $service = null): array
    {
        return [
            'class' => User::class,
            'choice_label' => 'fullname',
            'query_builder' => function (UserRepository $repo) use ($service) {
                return $repo->getSupportReferentsQueryBuilder($service, $this->currentUserService->getUser());
            },
            'placeholder' => 'placeholder.select',
            'required' => false,
        ];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SupportGroup::class,
            'translation_domain' => 'forms',
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'support';
    }
}
