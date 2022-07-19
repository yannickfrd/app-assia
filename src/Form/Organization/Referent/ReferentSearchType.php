<?php

namespace App\Form\Organization\Referent;

use App\Entity\Organization\Referent;
use App\Form\Model\Organization\ReferentSearch;
use App\Form\Utils\Choices;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReferentSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', SearchType::class, [
                'attr' => [
                    'placeholder' => 'Service name',
                ],
                'required' => false,
            ])
            ->add('socialWorker', SearchType::class, [
                'attr' => [
                    'placeholder' => 'SocialWorker name',
                ],
                'required' => false,
            ])
            ->add('type', ChoiceType::class, [
                'choices' => Choices::getChoices(Referent::TYPE),
                'attr' => [
                    'class' => 'w-max-150',
                ],
                'placeholder' => 'placeholder.type',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ReferentSearch::class,
            'translation_domain' => 'forms',
            'allow_extra_fields' => true,
            'csrf_protection' => false,
        ]);
    }
}
