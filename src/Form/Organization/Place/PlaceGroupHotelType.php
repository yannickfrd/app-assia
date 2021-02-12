<?php

namespace App\Form\Organization\Place;

use App\Entity\Organization\Place;
use App\Entity\Organization\Service;
use App\Entity\Support\PlaceGroup;
use App\Repository\Organization\PlaceRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PlaceGroupHotelType extends AbstractType
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
                ->add('place', EntityType::class, [
                    'class' => Place::class,
                    'choice_label' => 'name',
                    'query_builder' => function (PlaceRepository $repo) use ($serviceId) {
                        return $repo->getPlacesQueryList($serviceId);
                    },
                    'label' => Service::SERVICE_PASH_ID === $serviceId ? 'hotelName' : 'place.name',
                    'placeholder' => 'placeholder.select',
                    'required' => false,
                ])
                ->add('startDate', DateType::class, [
                    'label' => Service::SERVICE_PASH_ID === $serviceId ? 'hotelSupport.startDate' : 'placeGroup.startDate',
                    'widget' => 'single_text',
                    'required' => false,
                ])
                ->add('comment');
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PlaceGroup::class,
            'translation_domain' => 'forms',
        ]);
    }
}
