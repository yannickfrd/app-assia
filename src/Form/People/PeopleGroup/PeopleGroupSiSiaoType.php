<?php

namespace App\Form\People\PeopleGroup;

use App\Entity\People\PeopleGroup;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PeopleGroupSiSiaoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            /** @var PeopleGroup */
            $peopleGroup = $event->getData();
            $form = $event->getForm();

            $form
                ->add('siSiaoId', null, [
                    'required' => true,
                    'empty_data' => $peopleGroup->getSiSiaoId(),
                ]);
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PeopleGroup::class,
            'translation_domain' => 'forms',
        ]);
    }
}
