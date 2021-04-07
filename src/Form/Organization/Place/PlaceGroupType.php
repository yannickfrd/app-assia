<?php

namespace App\Form\Organization\Place;

use App\Entity\Organization\Place;
use App\Entity\Support\PlaceGroup;
use App\Form\Utils\Choices;
use App\Repository\Organization\PlaceRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PlaceGroupType extends AbstractType
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
                'choices' => Choices::getChoices(PlaceGroup::END_REASON),
                'required' => false,
                'placeholder' => 'placeholder.select',
            ])
            ->add('commentEndReason')
            ->add('placePeople', CollectionType::class, [
                'entry_type' => PlacePersonType::class,
                'label_attr' => [
                    'class' => 'sr-only',
                ],
                'allow_add' => true,
                'allow_delete' => true,
                'delete_empty' => true,
                'required' => false,
            ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $place = $event->getData();

            $service = $place->getSupportGroup()->getService();

            $event->getForm()->add('place', EntityType::class, [
                'class' => Place::class,
                'choice_label' => 'name',
                'query_builder' => function (PlaceRepository $repo) use ($service) {
                    return $repo->getPlacesQueryBuilder($service);
                },
                'placeholder' => 'placeholder.select',
            ]);
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
