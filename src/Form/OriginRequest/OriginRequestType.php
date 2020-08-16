<?php

namespace App\Form\OriginRequest;

use App\Form\Utils\Choices;
use App\Entity\Organization;
use App\Entity\OriginRequest;
use App\Entity\Service;
use Symfony\Component\Form\AbstractType;
use App\Repository\OrganizationRepository;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class OriginRequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $serviceId = ($options['attr']['serviceId']);

        $builder
            ->add('organization', EntityType::class, [
                'class' => Organization::class,
                'choice_label' => 'name',
                'query_builder' => function (OrganizationRepository $repo) use ($serviceId) {
                    return $repo->getOrganizationsQueryList($serviceId);
                },
                'placeholder' => 'placeholder.select',
                'required' => $serviceId == Service::SERVICE_AVDL_ID ? true : false,
            ])
            ->add('organizationComment')
            ->add('orientationDate', DateType::class, [
                'label' => $serviceId == Service::SERVICE_AVDL_ID ? 'avdl.orientationDate' : '',
                'widget' => 'single_text',
                'required' => $serviceId == Service::SERVICE_AVDL_ID ? true : false,
            ])
            ->add('preAdmissionDate', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('resulPreAdmission', ChoiceType::class, [
                'choices' => Choices::getChoices(OriginRequest::RESULT_PRE_ADMISSION),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('decisionDate', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('comment', TextareaType::class, [
                'label_attr' => ['class' => 'sr-only'],
                'attr' => [
                    'rows' => 2,
                    'placeholder' => 'Write a comment about the origin request',
                ],
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => OriginRequest::class,
            'translation_domain' => 'forms',
        ]);
    }
}
