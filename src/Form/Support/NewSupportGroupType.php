<?php

namespace App\Form\Support;

use App\Entity\Accommodation;
use App\Entity\Device;
use App\Entity\Service;
use App\Entity\SubService;
use App\Entity\SupportGroup;
use App\Entity\User;
use App\Repository\AccommodationRepository;
use App\Repository\DeviceRepository;
use App\Repository\ServiceRepository;
use App\Repository\UserRepository;
use App\Security\CurrentUserService;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
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
                    return $repo->getServicesFromUserQueryList($this->currentUser);
                },
                'placeholder' => 'placeholder.select',
            ])
            ->add('subService', ChoiceType::class)
            ->add('device', ChoiceType::class)
            ->add('referent', HiddenType::class);

        $builder->get('service')->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();
            $service = $form->getData();

            $this->addFieldsAfterSubmitService($form->getParent(), $service);

            $builder = $this->getSubServiceBuilder($form->getParent(), $service);
            $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use ($service) {
                $this->addAccommodationField($event->getForm(), $service);
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
                'required' => false,
            ])
            ->add('device', EntityType::class, [
                'class' => Device::class,
                'choice_label' => 'name',
                'query_builder' => function (DeviceRepository $repo) use ($service) {
                    return $repo->getDevicesFromUserQueryList($this->currentUser, $service->getId());
                },
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('referent', EntityType::class, $optionsReferent)
            ->add('referent2', EntityType::class, $optionsReferent);
    }

    protected function addAccommodationField(FormInterface $form, Service $service)
    {
        $subService = $form->getData();

        $form->getParent()
            ->add('accommodation', EntityType::class, [
                'class' => Accommodation::class,
                'choice_label' => 'name',
                'query_builder' => function (AccommodationRepository $repo) use ($service, $subService) {
                    return $repo->getAccommodationsQueryList($service->getId(), $subService ? $subService->getId() : null);
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
                return $repo->getUsersQueryList($serviceId, $this->currentUser->getUser());
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
