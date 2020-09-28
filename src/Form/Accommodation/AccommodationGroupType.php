<?php

namespace App\Form\Accommodation;

use App\Entity\Accommodation;
use App\Entity\AccommodationGroup;
use App\Form\Utils\Choices;
use App\Repository\AccommodationRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AccommodationGroupType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('startDate', DateType::class, [
                'widget' => 'single_text',
                'required' => true,
            ])
            ->add('endDate', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('endReason', ChoiceType::class, [
                'choices' => Choices::getChoices(AccommodationGroup::END_REASON),
                'required' => false,
                'placeholder' => 'placeholder.select',
            ])
            ->add('commentEndReason')
            ->add('accommodationPeople', CollectionType::class, [
                'entry_type' => AccommodationPersonType::class,
                'label_attr' => [
                    'class' => 'sr-only',
                ],
                'allow_add' => true,
                'allow_delete' => true,
                'delete_empty' => true,
                'required' => false,
            ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $accommodation = $event->getData();

            $serviceId = $accommodation->getSupportGroup()->getService()->getId();

            $event->getForm()->add('accommodation', EntityType::class, [
                'class' => Accommodation::class,
                'choice_label' => 'name',
                'query_builder' => function (AccommodationRepository $repo) use ($serviceId) {
                    return $repo->getAccommodationsQueryList($serviceId);
                },
                'placeholder' => 'placeholder.select',
            ]);
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
