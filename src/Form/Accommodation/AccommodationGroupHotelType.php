<?php

namespace App\Form\Accommodation;

use App\Entity\Accommodation;
use App\Entity\AccommodationGroup;
use App\Entity\Service;
use App\Repository\AccommodationRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AccommodationGroupHotelType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $form = $event->getForm();
            $serviceId = null;
            $attr = $form->getParent()->getConfig()->getOption('attr');

            if ($attr['serviceId']) {
                $serviceId = $attr['serviceId'];
                // $subServiceId = $attr['subServiceId'];
            } else {
                $supportGroup = $form->getParent()->getParent()->getData();
                $service = $supportGroup->getService();
                $serviceId = $service ? $service->getId() : null;
                // $subServiceId = $supportGroup->getSubService() ? $supportGroup->getSubService()->getId() : null;
            }

            $form
                ->add('accommodation', EntityType::class, [
                    'class' => Accommodation::class,
                    'choice_label' => 'name',
                    'query_builder' => function (AccommodationRepository $repo) use ($serviceId) {
                        return $repo->getAccommodationsQueryList($serviceId);
                    },
                    'label' => Service::SERVICE_PASH_ID == $serviceId ? 'hotelName' : 'accommodation.name',
                    'placeholder' => 'placeholder.select',
                    'required' => false,
                ])
                ->add('startDate', DateType::class, [
                    'label' => Service::SERVICE_PASH_ID == $serviceId ? 'hotelSupport.startDate' : 'accommodationGroup.startDate',
                    'widget' => 'single_text',
                    'required' => false,
                ])
                ->add('comment');
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => AccommodationGroup::class,
            'translation_domain' => 'forms',
        ]);
    }
}
