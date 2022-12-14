<?php

namespace App\Form\Organization\Place;

use App\Entity\Organization\Pole;
use App\Form\Model\Event\EventSearch;
use App\Form\Model\Organization\PlaceSearch;
use App\Form\Type\DateSearchType;
use App\Form\Type\ServiceDeviceReferentSearchType;
use App\Form\Utils\Choices;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;

class PlaceSearchType extends AbstractType
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('service', ServiceDeviceReferentSearchType::class, [
                'data_class' => EventSearch::class,
            ])
            ->add('name', SearchType::class, [
                'attr' => [
                    'class' => 'w-max-140',
                    'placeholder' => 'Name',
                ],
                'required' => false,
            ])
            ->add('nbPlaces', null, [
                'attr' => [
                    'class' => 'w-max-140',
                    'placeholder' => 'Places number',
                ],
            ])
            ->add('supportDates', ChoiceType::class, [
                'choices' => Choices::getChoices(PlaceSearch::PLACE_DATES),
                'placeholder' => '-- Date --',
                'required' => false,
            ])
            ->add('city', SearchType::class, [
                'attr' => [
                    'class' => 'w-max-140',
                    'placeholder' => 'City',
                ],
                'required' => false,
            ])
            ->add('date', DateSearchType::class, [
                'data_class' => EventSearch::class,
            ])
            ->add('disabled', ChoiceType::class, [
                'choices' => Choices::getChoices(Choices::DISABLE),
                'placeholder' => 'placeholder.disabled',
                'required' => false,
            ])
            ->add('export');

        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {
            $form = $event->getForm();

            if ($this->security->isGranted('ROLE_SUPER_ADMIN')) {
                $form
                    ->add('pole', EntityType::class, [
                        'class' => Pole::class,
                        'choice_label' => 'name',
                        'placeholder' => 'placeholder.pole',
                        'required' => false,
                ]);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PlaceSearch::class,
            'method' => 'get',
            'translation_domain' => 'forms',
            'csrf_protection' => false,
            'allow_extra_fields' => true,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}
