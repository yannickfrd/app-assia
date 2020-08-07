<?php

namespace App\Form\Accommodation;

use App\Form\Utils\Choices;
use App\Entity\Accommodation;
use App\Entity\AccommodationGroup;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use App\Repository\AccommodationRepository;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class AccommodationGroupType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('startDate', DateType::class, [
                'widget' => 'single_text',
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

            $service = $accommodation->getSupportGroup()->getService();

            $event->getForm()->add('accommodation', EntityType::class, [
                'class' => Accommodation::class,
                'choice_label' => 'name',
                'query_builder' => function (AccommodationRepository $repo) use ($service) {
                    return $repo->getAccommodationsQueryList($service);
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
