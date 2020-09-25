<?php

namespace App\Form\Accommodation;

use App\Entity\Accommodation;
use App\Entity\AccommodationGroup;
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
            $service = $event->getData()->getSupportGroup()->getService();

            $event->getForm()
                ->add('accommodation', EntityType::class, [
                    'class' => Accommodation::class,
                    'choice_label' => 'name',
                    'query_builder' => function (AccommodationRepository $repo) use ($service) {
                        return $repo->getAccommodationsQueryList($service);
                    },
                    'label' => 'hotelName',
                    'placeholder' => 'placeholder.select',
                    'required' => true,
                ])
                ->add('startDate', DateType::class, [
                    'label' => 'hotelSupport.startDate',
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
