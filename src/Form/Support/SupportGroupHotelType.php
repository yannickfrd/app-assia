<?php

namespace App\Form\Support;

use App\Entity\AccommodationGroup;
use App\Entity\SupportGroup;
use App\Form\Accommodation\AccommodationGroupHotelType;
use App\Form\HotelSupport\HotelSupportType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class SupportGroupHotelType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->remove('status')
            ->remove('location')
            ->add('hotelSupport', HotelSupportType::class);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $form = $event->getForm();
            $supportGroup = $event->getData();
            $serviceId = $supportGroup->getService() ? $supportGroup->getService()->getId() : null;
            $subServiceId = $supportGroup->getSubService() ? $supportGroup->getSubService()->getId() : null;

            if ($supportGroup->getAccommodationGroups()->count() == 0) {
                $this->addAccommodationGroup($supportGroup);
            }

            $form->add('accommodationGroups', CollectionType::class, [
                'entry_type' => AccommodationGroupHotelType::class,
                'label' => null,
                'allow_add' => false,
                'allow_delete' => false,
                'delete_empty' => true,
                'attr' => [
                    'serviceId' => $serviceId,
                    'subServiceId' => $subServiceId,
                ],
            ]);
        });
    }

    protected function addAccommodationGroup(SupportGroup $supportGroup)
    {
        $accommodationGroup = (new AccommodationGroup())->setGroupPeople($supportGroup->getGroupPeople());
        $supportGroup->addAccommodationGroup($accommodationGroup);

        return $supportGroup;
    }

    public function getParent()
    {
        return SupportGroupType::class;
    }

    public function getBlockPrefix()
    {
        return 'support';
    }
}
