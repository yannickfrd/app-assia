<?php

namespace App\Form\Evaluation;

use App\Entity\Evaluation\EvaluationPerson;
use App\Form\Utils\Choices;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EvaluationPersonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('initEvalPerson', InitEvalPersonType::class)
            ->add('evalAdmPerson', EvalAdmPersonType::class)
            ->add('evalBudgetPerson', EvalBudgetPersonType::class)
            ->add('evalFamilyPerson', EvalFamilyPersonType::class)
            ->add('evalProfPerson', EvalProfPersonType::class)
            ->add('evalSocialPerson', EvalSocialPersonType::class);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $evaluationPerson = $event->getData();
            $service = $evaluationPerson->getEvaluationGroup()->getSupportGroup()->getService();
            if (Choices::YES == $service->getJustice()) {
                $event->getForm()->add('evalJusticePerson', EvalJusticePersonType::class);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => EvaluationPerson::class,
        ]);
    }
}
