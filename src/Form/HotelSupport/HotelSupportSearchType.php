<?php

namespace App\Form\HotelSupport;

use App\Entity\Accommodation;
use App\Entity\HotelSupport;
use App\Form\Model\HotelSupportSearch;
use App\Form\Support\SupportSearchType;
use App\Form\Utils\Choices;
use App\Repository\AccommodationRepository;
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
            ->add('hotels', EntityType::class, [
                'class' => Accommodation::class,
                'choice_label' => 'name',
                'multiple' => true,
                'query_builder' => function (AccommodationRepository $repo) {
                    return $repo->createQueryBuilder('a')
                        ->select('PARTIAL a.{id, name}')
                        ->where('a.service = :service')
                        ->setParameter('service', 10)
                        ->orderBy('a.name', 'ASC');
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
                'choices' => Choices::getChoices(HotelSupport::LEVEL_SUPPORT),
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
                'choices' => Choices::getChoices(HotelSupport::END_SUPPORT_REASON),
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
