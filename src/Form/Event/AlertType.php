<?php

namespace App\Form\Event;

use App\Entity\Event\Alert;
use App\Form\Utils\Choices;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AlertType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('date', DateTimeType::class, [
                'widget' => 'single_text',
            ])
            ->add('type', ChoiceType::class, [
                'choices' => Choices::getChoices(Alert::TYPE),
                'attr' => ['class' => 'form-control-sm'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Alert::class,
            'translation_domain' => 'forms',
        ]);
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}
