<?php

namespace App\Form\Contribution;

use App\Form\Utils\Choices;
use App\Entity\Contribution;
use App\Form\Type\DateSearchType;
use App\Form\Model\ContributionSearch;
use Symfony\Component\Form\AbstractType;
use App\Form\Model\SupportContributionSearch;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class SupportContributionSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', ChoiceType::class, [
                'label_attr' => [
                    'class' => 'sr-only',
                ],
                'choices' => Choices::getchoices(Contribution::CONTRIBUTION_TYPE),
                'attr' => [
                    'class' => 'w-max-150',
                ],
                'placeholder' => '-- Type --',
                'required' => false,
            ])
            ->add('date', DateSearchType::class, [
                'data_class' => ContributionSearch::class,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SupportContributionSearch::class,
            'translation_domain' => 'forms',
            'allow_extra_fields' => true,
            'csrf_protection' => false,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'search';
    }
}
