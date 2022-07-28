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
            ->add($attr['fullAddress'] ?? 'fullAddress', null, [
                'label' => $attr['location_search_label'] ?? '',
                'attr' => [
                    'placeholder' => $attr['location_search_placeholder'] ?? 'location.search.address.placeholder',
                ],
                'help' => $attr['location_search_help'] ?? 'location.search.help',
            ])
            ->add($attr['city'] ?? 'city', HiddenType::class, [
                'attr' => ['readonly' => true],
            ])
            ->add($attr['zipcode'] ?? 'zipcode', HiddenType::class, [
                'attr' => ['readonly' => true],
            ])
        ;

        if (false === isset($attr['address']) || false !== $attr['address']) {
            $builder->add($attr['address'] ?? 'address', HiddenType::class, [
                'attr' => ['readonly' => true],
            ]);
        }
        if (false === isset($attr['comment']) || false !== $attr['comment']) {
            $builder->add($attr['comment'] ?? 'commentLocation', null, [
                'label' => $attr['location_comment_label'] ?? 'location.comment',
                'help' => $attr['location_comment_help'] ?? 'location.comment.help',
            ]);
        }
        if (true === isset($attr['geo_location']) && true === $attr['geo_location']) {
            $builder
                ->add('locationId', HiddenType::class)
                ->add('lat', HiddenType::class)
                ->add('lon', HiddenType::class)
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'inherit_data' => true,
        ]);
    }
}
