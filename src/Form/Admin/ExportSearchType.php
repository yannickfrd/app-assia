<?php

namespace App\Form\Admin;

use App\Entity\People\PeopleGroup;
use App\Form\Model\Admin\ExportSearch;
use App\Form\Support\Support\SupportSearchType;
use App\Repository\Admin\ExportModelRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;

class ExportSearchType extends AbstractType
{
    public function __construct(
        private ExportModelRepository $exportModelRepo,
        private Security $security,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('head', CheckboxType::class, [
                'label' => 'head.help',
                'required' => false,
                ])
            ->add('calcul', null, ['mapped' => false])
            ->add('familyTypologies', ChoiceType::class, [
                'multiple' => true,
                'choices' => array_flip(PeopleGroup::FAMILY_TYPOLOGY),
                'attr' => [
                    'placeholder' => 'placeholder.familtyTypology',
                    'size' => 1,
                ],
                'required' => false,
            ])
            ->add('model', ChoiceType::class, [
                'choices' => $this->getModels(),
                'attr' => ['autocomplete' => 'true'],
            ])
            ->add('formattedSheet', CheckboxType::class, [
                'required' => false,
            ])
            ->add('anonymized', CheckboxType::class, [
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ExportSearch::class,
            'csrf_protection' => false,
            'method' => 'post',
            'translation_domain' => 'forms',
        ]);
    }

    public function getParent(): ?string
    {
        return SupportSearchType::class;
    }

    private function getModels(): array
    {
        $exportModels = $this->exportModelRepo->findBy([
            'createdBy' => $this->security->getUser(),
        ], ['title' => 'ASC']);

        $models = [
        'Modèles par défaut' => array_flip(ExportSearch::MODELS),
        ];

        if (count($exportModels) > 0) {
            foreach ($exportModels as $exportModel) {
                $models['Modèles personnalisés'][$exportModel->getTitle()] = $exportModel->getId();
            }
        }

        return $models;
    }
}
