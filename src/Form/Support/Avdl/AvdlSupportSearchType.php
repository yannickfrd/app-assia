<?php

namespace App\Form\Support\Avdl;

use App\Entity\Support\Avdl;
use App\Form\Model\Support\AvdlSupportSearch;
use App\Form\Support\Support\SupportSearchType;
use App\Form\Utils\Choices;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AvdlSupportSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('diagOrSupport', ChoiceType::class, [
                'choices' => Choices::getChoices(AvdlSupportSearch::DIAG_OR_SUPPORT),
                'placeholder' => '-- Diag/Acc. --',
                'required' => false,
            ])
            ->add('supportType', ChoiceType::class, [
                'multiple' => true,
                'choices' => Choices::getChoices(Avdl::SUPPORT_TYPE),
                'attr' => [
                    'class' => 'multi-select w-min-240 w-max-260',
                    'placeholder' => 'placeholder.supportType',
                    'size' => 1,
                ],
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => AvdlSupportSearch::class,
        ]);
    }

    public function getParent(): ?string
    {
        return SupportSearchType::class;
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}
