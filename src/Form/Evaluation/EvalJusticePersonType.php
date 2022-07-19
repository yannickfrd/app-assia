<?php

namespace App\Form\Evaluation;

use App\Entity\Evaluation\EvalJusticePerson;
use App\Form\Utils\Choices;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EvalJusticePersonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('justiceStatus', ChoiceType::class, [
                'choices' => Choices::getChoices(EvalJusticePerson::JUSTICE_STATUS),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('justiceAct', ChoiceType::class, [
                'choices' => Choices::getChoices(EvalJusticePerson::JUSTICE_ACT),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('commentEvalJustice', TextareaType::class, [
                'attr' => [
                    'rows' => 4,
                    'class' => 'justify',
                    'placeholder' => 'evalJusticePerson.comment',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => EvalJusticePerson::class,
            'translation_domain' => 'evaluation',
        ]);
    }
}
