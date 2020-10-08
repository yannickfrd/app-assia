<?php

namespace App\Form\Accommodation;

use App\Entity\Pole;
use App\Form\Model\AccommodationSearch;
use App\Form\Model\RdvSearch;
use App\Form\Type\DateSearchType;
use App\Form\Type\ServiceSearchType;
use App\Form\Utils\Choices;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;

class AccommodationSearchType extends AbstractType
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('service', ServiceSearchType::class, [
                'data_class' => RdvSearch::class,
            ])
            ->add('name', null, [
                'label_attr' => [
                    'class' => 'sr-only',
                ],
                'attr' => [
                    'class' => 'w-max-140',
                    'placeholder' => 'Name',
                ],
            ])
            ->add('nbPlaces', null, [
                'label_attr' => [
                    'class' => 'sr-only',
                ],
                'attr' => [
                    'class' => 'w-max-140',
                    'placeholder' => 'Places number',
                ],
            ])
            ->add('supportDates', ChoiceType::class, [
                'label_attr' => ['class' => 'sr-only'],
                'choices' => Choices::getChoices(AccommodationSearch::ACCOMMODATION_DATES),
                'placeholder' => '-- Date --',
                'required' => false,
            ])
            ->add('city', null, [
                'label_attr' => [
                    'class' => 'sr-only',
                ],
                'attr' => [
                    'class' => 'w-max-140',
                    'placeholder' => 'City',
                ],
            ])
            ->add('date', DateSearchType::class, [
                'data_class' => RdvSearch::class,
            ])
            ->add('disabled', ChoiceType::class, [
                'label_attr' => ['class' => 'sr-only'],
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
                        'label_attr' => ['class' => 'sr-only'],
                        'placeholder' => 'placeholder.pole',
                        'required' => false,
                ]);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => AccommodationSearch::class,
            'method' => 'get',
            'translation_domain' => 'forms',
            'csrf_protection' => false,
            'allow_extra_fields' => true,
        ]);
    }

    public function getBlockPrefix()
    {
        return '';
    }
}
