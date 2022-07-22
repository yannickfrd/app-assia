<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LocationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $attr = $builder->getOption('attr');

        $builder
            ->add('_search', null, [
                'label' => $attr['seachLabel'] ?? '',
                'attr' => [
                    'data-location-type' => 'search',
                    'placeholder' => 'location.search.address.placeholder',
                    'autocomplete' => 'off',
                ],
                'help' => $attr['searchHelp'] ?? null,
                'mapped' => false,
            ])
            ->add('commentLocation', null, [
                'help' => $attr['commentLocationHelp'] ?? 'commentLocation.help',
            ])
            ->add('address', null, [
                'label' => 'location.address_auto',
                'attr' => [
                    'data-location-type' => 'address',
                    'readonly' => true,
                ],
            ])
            ->add('city', null, [
                'label' => 'location.city_auto',
                'attr' => [
                    'data-location-type' => 'city',
                    'readonly' => true,
                ],
            ])
            ->add('zipcode', null, [
                'label' => 'location.zipcode_auto',
                'attr' => [
                    'data-location-type' => 'zipcode',
                    'readonly' => true,
                ],
            ]);

        if (isset($attr['geoLocation']) && true === $attr['geoLocation']) {
            $builder
                ->add('locationId', HiddenType::class, [
                    'attr' => [
                        'data-location-type' => 'locationId',
                        'readonly' => true,
                    ],
                ])
                ->add('lat', HiddenType::class, [
                    'attr' => [
                        'data-location-type' => 'lat',
                        'readonly' => true,
                    ],
                ])
                ->add('lon', HiddenType::class, [
                    'attr' => [
                        'data-location-type' => 'lon',
                        'readonly' => true,
                    ],
                ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'inherit_data' => true,
            'allow_extra_fields' => true,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}
