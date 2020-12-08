<?php

namespace App\Form\Avdl;

use App\Entity\Avdl;
use App\Form\Model\AvdlSupportSearch;
use App\Form\Support\SupportSearchType;
use App\Form\Utils\Choices;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AvdlSupportSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('diagOrSupport', ChoiceType::class, [
                'label_attr' => ['class' => 'sr-only'],
                'choices' => Choices::getChoices(AvdlSupportSearch::DIAG_OR_SUPPORT),
                'placeholder' => '-- Diag/Acc. --',
                'required' => false,
            ])
            ->add('supportType', ChoiceType::class, [
                'label_attr' => ['class' => 'sr-only'],
                'multiple' => true,
                'choices' => Choices::getChoices(Avdl::SUPPORT_TYPE),
                'attr' => [
                    'class' => 'multi-select w-min-120',
                    'data-select2-id' => 'support-type',
                ],
                'placeholder' => 'placeholder.type',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => AvdlSupportSearch::class,
        ]);
    }

    public function getParent()
    {
        return SupportSearchType::class;
    }

    public function getBlockPrefix()
    {
        return '';
    }
}
