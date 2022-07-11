<?php

namespace App\Form\Support\Support;

use App\Entity\People\RolePerson;
use App\Entity\Support\SupportGroup;
use App\Entity\Support\SupportPerson;
use App\Form\Utils\Choices;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\RadioType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SupportPersonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('head', RadioType::class, [
                'label' => false,
                'required' => false,
            ])
            ->add('role', ChoiceType::class, [
                'choices' => Choices::getChoices(RolePerson::ROLE),
                'placeholder' => 'placeholder.select',
                'required' => true,
            ])
            ->add('startDate', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('endDate', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('endStatus', ChoiceType::class, [
                'choices' => Choices::getChoices(SupportGroup::END_STATUS),
                'placeholder' => 'placeholder.select',
            ])
            ->add('endStatusComment')
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $form = $event->getForm();

            if (!$form->getParent()) {
                return;
            }

            $form
                ->add('endReason', ChoiceType::class, [
                    'choices' => $form->getParent()->getParent()->get('endReason')->getConfig()->getOption('choices'),
                    'placeholder' => 'placeholder.select',
                    'required' => true,
                ])
            ;
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SupportPerson::class,
            'translation_domain' => 'forms',
        ]);
    }
}
