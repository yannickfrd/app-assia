<?php

namespace App\Form\Evaluation;

use App\Entity\Service;
use App\Entity\EvaluationGroup;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class EvaluationGroupType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
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
            if (in_array($service->getId(), Service::SERVICES_PAMH_ID)) {
                $event->getForm()->add('evalHotelLifeGroup', EvalHotelLifeGroupType::class);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => EvaluationGroup::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'evaluation';
    }
}
