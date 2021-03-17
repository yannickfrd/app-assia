<?php

namespace App\Form;

use App\Entity\Support\HousingProposal;
use App\Form\Type\LocationType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HousingProposalType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $form = $event->getForm();
            $form
                ->add('location', LocationType::class, [
                    'data_class' => HousingProposal::class,
                    'attr' => [
                        'geoLocation' => false,
                        'searchHelp' => 'location.search.help',
                    ],
                ])
            ;
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => HousingProposal::class,
            'translation_domain' => 'forms',
        ]);
    }
}
