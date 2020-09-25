<?php

namespace App\Form\HotelSupport;

use App\Entity\Accommodation;
use App\Entity\EvalHousingGroup;
use App\Entity\HotelSupport;
use App\Form\Utils\Choices;
use App\Repository\AccommodationRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HotelSupportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            // $hotelSupport = $event->getData();
            // $supportGroup = $hotelSupport->getSupportGroup();

            $event->getForm()
                ->add('entryHotelDate', DateType::class, [
                    'widget' => 'single_text',
                    'required' => false,
                ])
                ->add('originDept', ChoiceType::class, [
                    'choices' => Choices::getChoices(Choices::DEPARTMENTS),
                    'placeholder' => 'placeholder.select',
                    'required' => false,
                ])
                ->add('gipId')
                ->add('searchSsd', null, [
                    'label' => 'hotelSupport.ssd.search',
                    'attr' => [
                        'class' => 'js-search',
                        'placeholder' => 'hotelSupport.ssd.search.placeholder',
                        'autocomplete' => 'off',
                    ],
                    'help' => null,
                    'mapped' => false,
                ])
                ->add('ssd', null, [
                    'label' => 'hotelSupport.ssd.city',
                    'attr' => [
                        'class' => 'js-city',
                        'readonly' => true,
                    ],
                ])
                // ->add('hotel', EntityType::class, [
                //     'class' => Accommodation::class,
                //     'choice_label' => 'name',
                //     'query_builder' => function (AccommodationRepository $repo) use ($supportGroup) {
                //         return $repo->createQueryBuilder('a')
                //         ->select('PARTIAL a.{id, name}')

                //         ->where('a.disabledAt IS NULL')
                //         ->andWhere('a.service = :service')
                //         ->setParameter('service', $supportGroup->getService())
                //         // ->andWhere('a.subService = :subService')
                //         // ->setParameter('subService', $supportGroup->getSubService())
                //         ->orderBy('a.name', 'ASC');
                //     },
                //     'label' => 'hotelSupport.hotelName',
                //     'placeholder' => 'placeholder.select',
                //     'required' => false,
                // ])
                ->add('evaluationDate', DateType::class, [
                    'widget' => 'single_text',
                    'required' => false,
                ])
                ->add('levelSupport', ChoiceType::class, [
                    'choices' => Choices::getChoices(HotelSupport::LEVEL_SUPPORT),
                    'placeholder' => 'placeholder.select',
                    'required' => false,
                ])
                ->add('agreementDate', DateType::class, [
                    'widget' => 'single_text',
                    'required' => false,
                ])
                ->add('departmentAnchor', ChoiceType::class, [
                    'choices' => Choices::getChoices(Choices::DEPARTMENTS),
                    'placeholder' => 'placeholder.select',
                    'required' => false,
                    ])
                ->add('recommendation', ChoiceType::class, [
                    'choices' => Choices::getChoices(EvalHousingGroup::SIAO_RECOMMENDATION),
                    'placeholder' => 'placeholder.select',
                    'required' => false,
                ])
                ->add('endSupportReason', ChoiceType::class, [
                    'choices' => Choices::getChoices(HotelSupport::END_SUPPORT_REASON),
                    'placeholder' => 'placeholder.select',
                    'required' => false,
                ])
                ->add('endSupportComment', TextareaType::class, [
                    'attr' => [
                        'rows' => 2,
                        'placeholder' => 'avdl.endSupportComment.placeholder',
                    ],
                    'required' => false,
                ]);
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => HotelSupport::class,
            'translation_domain' => 'forms',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'hotel';
    }
}
