<?php

namespace App\Form\Organization\Place;

use App\Entity\Support\PlaceGroup;
use App\Entity\Support\SupportPerson;
use App\Repository\Support\SupportPersonRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddPersonToPlaceGroupType extends AbstractType
{
    protected $supportPersonRepo;

    public function __construct(SupportPersonRepository $supportPersonRepo)
    {
        $this->supportPersonRepo = $supportPersonRepo;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var PlaceGroup $placeGroup */
        $placeGroup = $options['attr']['placeGroup'];

        $builder
            ->add('supportPerson', EntityType::class, [
                'class' => SupportPerson::class,
                'choice_label' => function (SupportPerson $supportPerson) {
                    return $supportPerson->getPerson()->getFullname();
                },
                'choices' => $this->supportPersonRepo->findPeopleNotInPlaceGroup($placeGroup),
                'placeholder' => 'placeholder.person',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'forms',
        ]);
    }
}
