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
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $form = $event->getForm();
            /** @var EvaluationPerson */
            $evaluationPerson = $event->getData();
            $service = $evaluationPerson->getEvaluationGroup()->getSupportGroup()->getService();
            $supportPerson = $evaluationPerson->getSupportPerson();
            $person = $supportPerson->getPerson();

            $form
                ->add('initEvalPerson', InitEvalPersonType::class, [
                    'attr' => ['person' => $person],
                ])
                ->add('evalAdmPerson', EvalAdmPersonType::class)
                ->add('evalFamilyPerson', EvalFamilyPersonType::class, [
                    'attr' => ['supportPerson' => $supportPerson],
                    ])
                ->add('evalSocialPerson', EvalSocialPersonType::class, [
                    'attr' => ['supportPerson' => $supportPerson],
                ]);

            if ($person->getAge() >= 16) {
                $form->add('evalProfPerson', EvalProfPersonType::class)
                    ->add('evalBudgetPerson', EvalBudgetPersonType::class);
            }

            if (Choices::YES === $service->getJustice()) {
                $form->add('evalJusticePerson', EvalJusticePersonType::class);
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
