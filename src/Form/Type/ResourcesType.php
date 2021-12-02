<?php

namespace App\Form\Type;

use App\Entity\Evaluation\EvalBudgetPerson;
use App\Form\Utils\Choices;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ResourcesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('resources', ChoiceType::class, [
            'choices' => Choices::getChoices(EvalBudgetPerson::RESOURCES),
            'attr' => [
                'data-twin-field' => 'resources',
                'data-important' => 'true',
            ],
            'placeholder' => 'placeholder.select',
            'required' => false,
        ])
        ->add('resourcesAmt', MoneyType::class, [
            'attr' => [
                'class' => 'text-right',
                'data-amount' => 'resourcesAmt',
                'data-important' => 'true',
                'data-twin-field' => 'resourcesAmt',
                'placeholder' => 'Amount',
            ],
            'required' => false,
        ])
        ->add('ressourceOtherPrecision', null, [
            'attr' => [
                'data-twin-field' => 'ressourceOtherPrecision',
                'placeholder' => 'Other ressource(s)...',
            ],
        ]);

        foreach (EvalBudgetPerson::RESOURCES_TYPE as $key => $value) {
            $builder
            ->add($key, IntegerType::class, [
                'attr' => [
                    'data-twin-field' => $key,
                ],
            ])
            ->add($key.'Amt', MoneyType::class, [
                'attr' => [
                    'class' => 'text-right',
                    'data-amount' => 'resources',
                    'data-twin-field' => $key.'Amt',
                    'data-important' => 'true',
                    'placeholder' => 'Amount',
                ],
                'required' => false,
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'inherit_data' => true,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}
