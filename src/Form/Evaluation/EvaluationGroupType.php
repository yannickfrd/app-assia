<?php

namespace App\Form\Evaluation;

use App\Entity\Evaluation\EvaluationGroup;
use App\Entity\Organization\Service;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EvaluationGroupType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('backgroundPeople', null, [
                'label_attr' => ['class' => 'sr-only'],
                'attr' => [
                    'rows' => 5,
                    'class' => 'justify',
                    'placeholder' => 'backgroundPeople.placeholder',
                ],
            ])
            ->add('conclusion', null, [
                'label_attr' => ['class' => 'sr-only'],
                'attr' => [
                    'rows' => 5,
                    'class' => 'justify',
                    'placeholder' => 'conclusion.placeholder',
                ],
            ])
            ->add('initEvalGroup', InitEvalGroupType::class)
            ->add('evalSocialGroup', EvalSocialGroupType::class)
            ->add('evalFamilyGroup', EvalFamilyGroupType::class)
            ->add('evalBudgetGroup', EvalBudgetGroupType::class)
            ->add('evalHousingGroup', EvalHousingGroupType::class)
            ->add('evaluationPeople', CollectionType::class, [
                'entry_type' => EvaluationPersonType::class,
                'allow_add' => false,
                'allow_delete' => false,
                'required' => false,
            ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $evaluationGroup = $event->getData();
            $service = $evaluationGroup->getSupportGroup()->getService();
            if (Service::SERVICE_PASH_ID === $service->getId()) {
                $event->getForm()->add('evalHotelLifeGroup', EvalHotelLifeGroupType::class);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => EvaluationGroup::class,
                'translation_domain' => 'evaluation',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'evaluation';
    }
}
