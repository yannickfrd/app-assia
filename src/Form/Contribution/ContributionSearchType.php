<?php

namespace App\Form\Contribution;

use App\Form\Utils\Choices;
use App\Entity\Contribution;
use App\Form\Type\ServiceSearchType;
use App\Form\Type\DateListSearchType;
use App\Form\Model\ContributionSearch;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class ContributionSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('fullname', null, [
                'label_attr' => ['class' => 'sr-only'],
                'attr' => [
                    'placeholder' => 'Suivi concerné',
                    'class' => 'w-max-180',
                ],
            ])
            ->add('type', ChoiceType::class, [
                'label_attr' => [
                    'class' => 'sr-only',
                ],
                'choices' => Choices::getchoices(Contribution::CONTRIBUTION_TYPE),
                'attr' => [
                    'class' => 'w-max-180',
                ],
                'placeholder' => '-- Type --',
                'required' => false,
            ])
            ->add('date', DateListSearchType::class, [
                'data_class' => ContributionSearch::class,
            ])
            ->add('service', ServiceSearchType::class, [
                'data_class' => ContributionSearch::class,
            ])
            ->add('export');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ContributionSearch::class,
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
