<?php

namespace App\Form\Admin;

use App\Entity\Admin\ExportModel;
use App\Service\Export\ExportModelConverter;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;

class ExportModelType extends AbstractType
{
    public function __construct(
        private ExportModelConverter $exportModelConverter
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', null)
            ->add('comment', TextareaType::class, [
                'required' => false,
            ])
            ->add('share', CheckboxType::class, [
                'help' => 'model_export.share.help',
                'required' => false,
            ])
        ;

        if (null !== $builder->getOption('entities')) {
            foreach ($builder->getOption('entities') as $entityItems) {
                foreach ($entityItems as $item) {
                    $builder
                        ->add($item['name'], CheckboxType::class, [
                            'label' => $item['trans_name'],
                            'required' => false,
                            'mapped' => false,
                        ]);
                }
            }
        }

        /** @var ExportModel $exportModel */
        $exportModel = $builder->getData();
        $content = $exportModel->getContent();

        if (null !== $content) {
            $nbItems = count($content);
            foreach ($content as $item) {
                $builder
                    ->add($item['id'], IntegerType::class, [
                        'label' => $item['label'],
                        'data' => $item['order'],
                        'constraints' => [
                            new GreaterThanOrEqual(1),
                        ],
                        'attr' => [
                            'min' => 1,
                            'max' => $nbItems,
                        ],
                        'mapped' => false,
                    ]);
            }
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'entities' => null,
            'data_class' => ExportModel::class,
            'translation_domain' => 'forms',
            'method' => 'post',
        ]);
    }
}
