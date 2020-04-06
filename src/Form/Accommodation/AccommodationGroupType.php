<?php

namespace App\Form\Accommodation;

use App\Entity\Accommodation;
use App\Entity\AccommodationGroup;
use App\Form\Utils\Choices;
use App\Repository\AccommodationRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AccommodationGroupType extends AbstractType
{
    protected $service;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $accommodationGroup = $options['data'];
        $this->service = $accommodationGroup->getSupportGroup()->getService();

        $builder
            ->add('accommodation', EntityType::class, [
                'class' => Accommodation::class,
                'choice_label' => 'name',
                'query_builder' => function (AccommodationRepository $repo) {
                    return $repo->getAccommodationsQueryList($this->service);
                },
                'placeholder' => '-- Select --',
            ])
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
                'placeholder' => '-- Select --',
            ])
            ->add('commentEndReason')
            ->add('accommodationPersons', CollectionType::class, [
                'entry_type' => AccommodationPersonType::class,
                'label_attr' => [
                    'class' => 'sr-only',
                ],
                'allow_add' => true,
                'allow_delete' => true,
                'delete_empty' => true,
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => AccommodationGroup::class,
            'translation_domain' => 'forms',
        ]);
    }
}
