<?php

namespace App\Form\Evaluation;

use App\Entity\Evaluation\Country;
use App\Entity\Evaluation\EvalAdmPerson;
use App\Entity\Organization\Service;
use App\Form\Utils\Choices;
use App\Form\Utils\EvaluationChoices;
use App\Repository\Evaluation\CountryRepository;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EvalAdmPersonType extends AbstractType
{
    private $countryRepo;

    public function __construct(CountryRepository $countryRepo)
    {
        $this->countryRepo = $countryRepo;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Service */
        $service = $options['attr']['service'];

        $builder
            ->add('nationality', ChoiceType::class, [
                'choices' => Choices::getChoices(EvalAdmPerson::NATIONALITY),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('arrivalDate', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('country')
            ->add('paper', ChoiceType::class, [
                'choices' => Choices::getChoices(EvaluationChoices::YES_NO_IN_PROGRESS),
                'attr' => [
                    'data-important' => 'true',
                    'data-twin-field' => 'true',
                ],
                'placeholder' => 'placeholder.select',
                'help' => 'evalAdmPerson.paper.help',
                'required' => false,
            ])
            ->add('paperType', ChoiceType::class, [
                'choices' => Choices::getChoices(EvalAdmPerson::PAPER_TYPE),
                'attr' => [
                    'data-important' => 'true',
                    'data-twin-field' => 'true',
                ],
                'placeholder' => 'placeholder.select',
                'help' => 'evalAdmPerson.paperType.help',
                'required' => false,
            ])
            ->add('asylumBackground', ChoiceType::class, [
                'choices' => Choices::getChoices(EvaluationChoices::YES_NO),
                'attr' => ['data-important' => 'true'],
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('asylumStatus', ChoiceType::class, [
                'choices' => Choices::getChoices(EvalAdmPerson::ASYLUM_STATUS),
                'attr' => [
                    'data-important' => 'true',
                    'data-twin-field' => 'true',
                ],
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('agdrefId', null, [
                'attr' => ['data-mask-type' => 'number'],
            ])
            ->add('endValidPermitDate', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('renewalPermitDate', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('nbRenewals')
            ->add('workRight', ChoiceType::class, [
                'choices' => Choices::getChoices(EvaluationChoices::YES_NO_IN_PROGRESS),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('commentEvalAdmPerson', null, [
                'label_attr' => ['class' => 'sr-only'],
                'attr' => [
                    'rows' => 5,
                    'class' => 'justify',
                    'placeholder' => 'evalAdmPerson.comment',
                ],
            ])
        ;

        if (Service::SERVICE_TYPE_ASYLUM === $service->getType()) {
            $this->editAsylum($builder);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => EvalAdmPerson::class,
            'translation_domain' => 'evaluation',
        ]);
    }

    private function editAsylum(FormBuilderInterface $builder): void
    {
        $builder
            ->add('ofpraRegistrationId', null, [
                'attr' => ['data-mask-type' => 'number'],
            ])
            ->add('_country', ChoiceType::class, [
                'choices' => $this->getCountryChoices(),
                'choice_label' => 'name',
                'placeholder' => 'placeholder.select',
                'mapped' => false,
                'required' => false,
            ])
        ;

        if (!$builder->get('nationality')->getData()) {
            $builder->get('nationality')->setData(EvalAdmPerson::NATIONALITY_OUTSIDE_EU);
        }
    }

    private function getCountryChoices(): array
    {
        $cache = new FilesystemAdapter($_SERVER['DB_DATABASE_NAME']);

        return $cache->get(Country::CACHE_COUNTRY_ALL_KEY, fn () => $this->countryRepo->findAll());
    }
}
