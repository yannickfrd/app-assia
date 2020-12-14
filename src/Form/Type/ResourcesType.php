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
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('resources', ChoiceType::class, [
            'choices' => Choices::getChoices(EvalBudgetPerson::RESOURCES),
            'attr' => [
                'class' => 'js-initEval important',
                'data-id' => 'resources',
            ],
            'placeholder' => 'placeholder.select',
            'required' => false,
        ])
        ->add('resourcesAmt', MoneyType::class, [
            'attr' => [
                'class' => 'js-money js-resourcesAmt js-initEval important text-right',
                'data-id' => 'resourcesAmt',
            ],
            'required' => false,
        ])
        ->add('ressourceOtherPrecision', null, [
            'attr' => [
                'class' => 'js-initEval',
                'data-id' => 'ressourceOtherPrecision',
                'placeholder' => 'Other ressource(s)...',
            ],
        ]);

        foreach (EvalBudgetPerson::RESOURCES_TYPE as $key => $value) {
            $builder
            ->add($key, IntegerType::class, [
                'attr' => [
                    'class' => 'js-initEval',
                    'data-id' => $key,
                ],
            ])
            ->add($key.'Amt', MoneyType::class, [
                'attr' => [
                    'class' => 'js-money js-resources js-initEval text-right',
                    'data-id' => $key.'Amt',
                ],
                'required' => false,
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'inherit_data' => true,
        ]);
    }

    public function getBlockPrefix()
    {
        return '';
    }
}
