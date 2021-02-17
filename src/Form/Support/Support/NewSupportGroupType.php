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
    private $currentUser;

    public function __construct(CurrentUserService $currentUser)
    {
        $this->currentUser = $currentUser;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('service', EntityType::class, [
                'class' => Service::class,
                'choice_label' => 'name',
                'query_builder' => function (ServiceRepository $repo) {
                    return $repo->getServicesOfUserQueryBuilder($this->currentUser);
                },
                'placeholder' => 'placeholder.select',
            ])
            ->add('subService', ChoiceType::class)
            ->add('device', ChoiceType::class, ['placeholder' => 'placeholder.select'])
            ->add('cloneSupport', CheckboxType::class, [
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

    protected function addFieldsAfterSubmitService(FormInterface $form, Service $service)
    {
        $optionsReferent = $this->optionsReferent($service->getId());

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
                'query_builder' => function (DeviceRepository $repo) use ($service) {
                    return $repo->getDevicesOfUserQueryBuilder($this->currentUser, $service->getId());
                },
                'placeholder' => 'placeholder.select',
            ])
            ->add('referent', EntityType::class, $optionsReferent)
            ->add('referent2', EntityType::class, $optionsReferent);
    }

    protected function addPlaceField(FormInterface $form, Service $service)
    {
        $subService = $form->getData();

        $form->getParent()
            ->add('place', EntityType::class, [
                'class' => Place::class,
                'choice_label' => 'name',
                'query_builder' => function (PlaceRepository $repo) use ($service, $subService) {
                    return $repo->getPlacesQueryBuilder($service->getId(), $subService ? $subService->getId() : null);
                },
                'placeholder' => 'placeholder.select',
                'mapped' => false,
                'required' => false,
            ]);
    }

    protected function getSubServiceBuilder(FormInterface $form, Service $service)
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
    protected function optionsReferent(?int $serviceId): array
    {
        return [
            'class' => User::class,
            'choice_label' => 'fullname',
            'query_builder' => function (UserRepository $repo) use ($serviceId) {
                return $repo->getUsersQueryBuilder($serviceId, $this->currentUser->getUser());
            },
            'placeholder' => 'placeholder.select',
            'required' => false,
        ];
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
