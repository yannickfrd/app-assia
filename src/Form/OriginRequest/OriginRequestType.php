<?php

namespace App\Form\OriginRequest;

use App\Entity\Organization;
use App\Entity\OriginRequest;
use App\Form\Utils\Choices;
use App\Repository\OrganizationRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OriginRequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('organization', EntityType::class, [
                'class' => Organization::class,
                'choice_label' => 'name',
                'query_builder' => function (OrganizationRepository $repo) {
                    return $repo->createQueryBuilder('o')
                        ->select('o')
                        ->orderBy('o.name', 'ASC');
                },
                'placeholder' => '-- Select --',
            ])
            ->add('organizationComment')
            ->add('orientationDate', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('preAdmissionDate', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('resulPreAdmission', ChoiceType::class, [
                'choices' => Choices::getChoices(OriginRequest::RESULT_PRE_ADMISSION),
                'placeholder' => '-- Select --',
            ])
            ->add('decisionDate', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('comment', TextareaType::class, [
                'label_attr' => ['class' => 'sr-only'],
                'attr' => [
                    'rows' => 5,
                    'placeholder' => 'Write a comment about the origin request',
                ],
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => OriginRequest::class,
            'translation_domain' => 'originRequest',
        ]);
    }
}
