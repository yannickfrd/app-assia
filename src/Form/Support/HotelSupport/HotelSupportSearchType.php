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
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('status', ChoiceType::class, [
                'multiple' => true,
                'choices' => Choices::getChoices(HotelSupport::STATUS),
                'attr' => [
                    'class' => 'w-max-260',
                    'placeholder' => 'placeholder.status',
                    'size' => 1,
                ],
                'required' => false,
            ])
            ->add('hotels', EntityType::class, [
                'class' => Place::class,
                'choice_label' => 'name',
                'multiple' => true,
                'query_builder' => function (PlaceRepository $repo) {
                    return $repo->getHotelPlacesQueryBuilder();
                },
                'attr' => [
                    'class' => 'w-min-220 w-max-260',
                    'placeholder' => 'placeholder.hotels',
                    'size' => 1,
                ],
                'required' => false,
            ])
            ->add('priorityCriteria', ChoiceType::class, [
                'multiple' => true,
                'choices' => Choices::getChoices(HotelSupport::PRIORITY_CRITERIA),
                'attr' => [
                    'class' => 'w-min-220 w-max-220',
                    'placeholder' => 'hotel_support.priority_criteria.placeholder',
                    'size' => 1,
                ],
                'required' => false,
            ])
            ->add('levelSupport', ChoiceType::class, [
                'multiple' => true,
                'choices' => Choices::getChoices(HotelSupport::SUPPORT_LEVELS),
                'attr' => [
                    'class' => 'w-min-220 w-max-220',
                    'placeholder' => 'placeholder.supportLevels',
                    'size' => 1,
                ],
                'required' => false,
            ])
            ->add('departmentAnchor', ChoiceType::class, [
                'choices' => Choices::getChoices(Choices::DEPARTMENTS),
                'placeholder' => 'hotelSupport.search.departmentAnchor',
                'required' => false,
            ])
            ->add('endReasons', ChoiceType::class, [
                'multiple' => true,
                'choices' => Choices::getChoices(HotelSupport::END_REASONS),
                'attr' => [
                    'class' => 'w-max-220',
                    'placeholder' => 'support.endReasons.placeholder',
                    'size' => 1,
                ],
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => HotelSupportSearch::class,
        ]);
    }

    public function getParent(): ?string
    {
        return SupportSearchType::class;
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}
