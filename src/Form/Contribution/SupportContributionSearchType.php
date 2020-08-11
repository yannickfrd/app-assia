<?php

namespace App\Form\Contribution;

use App\Form\Utils\Choices;
use App\Entity\Contribution;
use App\Form\Model\ContributionSearch;
use Symfony\Component\Form\AbstractType;
use App\Form\Model\SupportContributionSearch;
use App\Form\Type\DateSearchType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class SupportContributionSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            // ->add('contributionId')
            ->add('type', ChoiceType::class, [
                'label_attr' => [
                    'class' => 'sr-only',
                ],
                'multiple' => true,
                'attr' => [
                    'class' => 'multi-select js-contribution-type',
                ],
                'choices' => Choices::getchoices(Contribution::CONTRIBUTION_TYPE),
                'required' => false,
            ])
            ->add('dateType', ChoiceType::class, [
                'label_attr' => ['class' => 'sr-only'],
                'choices' => Choices::getChoices(ContributionSearch::DATE_TYPE),
                'placeholder' => '-- Type de date --',
                'required' => false,
            ])
            ->add('date', DateSearchType::class, [
                'data_class' => ContributionSearch::class,
            ])
            ->add('export');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SupportContributionSearch::class,
            'method' => 'get',
            'translation_domain' => 'forms',
            'allow_extra_fields' => true,
            'csrf_protection' => false,
        ]);
    }

    public function getBlockPrefix()
    {
        return '';
    }
}
