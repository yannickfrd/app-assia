<?php

namespace App\Form\Support\HotelSupport;

use App\Entity\Organization\Place;
use App\Entity\Support\HotelSupport;
use App\Form\Model\Support\HotelSupportSearch;
use App\Form\Support\Support\SupportSearchType;
use App\Form\Utils\Choices;
use App\Repository\Organization\PlaceRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HotelSupportSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('status', ChoiceType::class, [
                'multiple' => true,
                'choices' => Choices::getChoices(HotelSupport::STATUS),
                'attr' => [
                    'class' => 'multi-select',
                    'data-select2-id' => 'status',
                ],
                'placeholder' => 'placeholder.status',
                'required' => false,
            ])
            ->add('hotels', EntityType::class, [
                'class' => Place::class,
                'choice_label' => 'name',
                'multiple' => true,
                'query_builder' => function (PlaceRepository $repo) {
                    return $repo->getHotelPlacesQueryBuilder();
                },
                'label_attr' => ['class' => 'sr-only'],
                'attr' => [
                    'class' => 'multi-select w-min-150 w-max-180',
                    'data-select2-id' => 'hotels',
                ],
                'required' => false,
            ])
            ->add('levelSupport', ChoiceType::class, [
                'label_attr' => ['class' => 'sr-only'],
                'multiple' => true,
                'choices' => Choices::getChoices(HotelSupport::SUPPORT_LEVELS),
                'attr' => [
                    'class' => 'multi-select w-min-120',
                    'data-select2-id' => 'levelSupport',
                ],
                'required' => false,
            ])
            ->add('departmentAnchor', ChoiceType::class, [
                'label_attr' => ['class' => 'sr-only'],
                'choices' => Choices::getChoices(Choices::DEPARTMENTS),
                'placeholder' => 'hotelSupport.search.departmentAnchor',
                'required' => false,
            ])
            ->add('endSupportReasons', ChoiceType::class, [
                'label_attr' => ['class' => 'sr-only'],
                'multiple' => true,
                'choices' => Choices::getChoices(HotelSupport::END_SUPPORT_REASONS),
                'attr' => [
                    'class' => 'multi-select w-min-120',
                    'data-select2-id' => 'endSupportReasons',
                ],
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => HotelSupportSearch::class,
        ]);
    }

    public function getParent()
    {
        return SupportSearchType::class;
    }

    public function getBlockPrefix()
    {
        return '';
    }
}
