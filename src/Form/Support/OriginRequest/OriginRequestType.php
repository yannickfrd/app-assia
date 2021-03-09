<?php

namespace App\Form\Support\OriginRequest;

use App\Entity\Organization\Organization;
use App\Entity\Organization\Service;
use App\Entity\Support\OriginRequest;
use App\Form\Utils\Choices;
use App\Repository\Organization\OrganizationRepository;
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
        $serviceId = $builder->getOption('attr')['serviceId'] ?? null;
        $required = in_array($serviceId, [
            Service::SERVICE_AVDL_ID,
            Service::SERVICE_PASH_ID,
        ]);

        $builder
            ->add('infoToSiaoDate', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('organization', EntityType::class, [
                'class' => Organization::class,
                'choice_label' => 'name',
                'query_builder' => function (OrganizationRepository $repo) use ($serviceId) {
                    return $repo->getOrganizationsQueryBuilder($serviceId);
                },
                'placeholder' => 'placeholder.select',
                'required' => $required,
            ])
            ->add('organizationComment')
            ->add('orientationDate', DateType::class, [
                'label' => Service::SERVICE_AVDL_ID === $serviceId ? 'avdl.orientationDate' : '',
                'widget' => 'single_text',
                'required' => $required,
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
                    'placeholder' => 'originRequest.comment',
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
