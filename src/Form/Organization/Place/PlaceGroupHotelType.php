<?php

namespace App\Form\Organization\Place;

use App\Entity\Organization\Place;
use App\Entity\Organization\Service;
use App\Entity\Support\PlaceGroup;
use App\Repository\Organization\PlaceRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PlaceGroupHotelType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $form = $event->getForm();
            $attr = $form->getParent()->getConfig()->getOption('attr');
            $supportGroup = $form->getParent()->getParent()->getData();
            /** * @var Service */
            $service = $attr['service'] ?? $supportGroup->getService();

            $form
                ->add('place', EntityType::class, [
                    'class' => Place::class,
                    'choice_label' => 'name',
                    'query_builder' => function (PlaceRepository $repo) use ($service) {
                        return $repo->getPlacesQueryBuilder($service);
                    },
                    'label' => Service::SERVICE_TYPE_HOTEL === $service->getType() ? 'hotelName' : 'place.name',
                    'attr' => ['data-select' => 'advanced'],
                    'placeholder' => 'placeholder.select',
                    'required' => true,
                ])
                ->add('comment');
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PlaceGroup::class,
            'translation_domain' => 'forms',
        ]);
    }
}
