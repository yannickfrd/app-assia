<?php

namespace App\Form\Support\Rdv;

use App\Entity\Organization\Tag;
use App\Entity\Support\Rdv;
use App\Form\Utils\Choices;
use App\Repository\Organization\TagRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RdvType extends AbstractType
{
    private $tagRepo;

    public function __construct(TagRepository $tagRepo)
    {
        $this->tagRepo = $tagRepo;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Rdv $rdv */
        $rdv = $options['data'];
        $supportGroup = $rdv->getSupportGroup();

        $builder
            ->add('title', null, [
                'attr' => [
                    'class' => 'font-weight-bold',
                    'placeholder' => 'rdv.placeholder.title',
                ],
            ])
            ->add('start', DateTimeType::class, [
                'widget' => 'single_text',
            ])
            ->add('end', DateTimeType::class, [
                'widget' => 'single_text',
            ])
            ->add('status', ChoiceType::class, [
                'choices' => Choices::getchoices(Rdv::STATUS),
                'placeholder' => 'placeholder.status',
                'required' => false,
            ])
            ->add('location', null, [
                'attr' => [
                    'class' => 'js-search',
                    'placeholder' => 'rdv.placeholder.location',
                    'autocomplete' => 'off',
                ],
            ])
            ->add('content', null, [
                'attr' => [
                    'rows' => 5,
                    'placeholder' => 'rdv.placeholder.content',
                ],
            ])
            ->add('tags', EntityType::class, [
                'class' => Tag::class,
                'label' => false,
                'multiple' => true,
                'expanded' => false,
                'by_reference' => false,
                'choices' => $supportGroup ?
                    $this->tagRepo->getTagsByService($supportGroup->getService(), 'rdv') :
                    $this->tagRepo->findAllTags('rdv'),
                'choice_label' => 'name',
                'attr' => [
                    'class' => 'multi-select',
                    'placeholder' => 'placeholder.tags',
                    'size' => 1,
                ],
                'required' => false,
            ])
            ->add('_googleCalendar', CheckboxType::class, [
                'label' => 'rdv.label.google',
                'label_attr' => ['class' => 'custom-control-label'],
                'attr' => ['class' => 'custom-control-input checkbox api-calendar'],
                'mapped' => false,
                'required' => false,
            ])
            ->add('_outlookCalendar', CheckboxType::class, [
                'label' => 'rdv.label.outlook',
                'label_attr' => ['class' => 'custom-control-label'],
                'attr' => ['class' => 'custom-control-input checkbox api-calendar'],
                'mapped' => false,
                'required' => false,
            ])
        // ->add('user', EntityType::class, [
            //     'class' => User::class,
            //     'choice_label' => 'fullname',
            //         'query_builder' => function (UserRepository $repo) {
            //             return $repo->getUsersForCalendarQueryBuilder($this->security->getUser());
            //         },
            //     'placeholder' => 'placeholder.user',
            //     'required' => true,
            // ])
            // ->add('supportGroup', EntityType::class, [
            //         'class' => SupportGroup::class,
            //         'choices' => $this->supportGroupRepo->getSupportsOfUserQueryBuilder($this->security->getUser()),
            //         'choice_label' => function (SupportGroup $supportGroup) {
            //             return $supportGroup->getSupportPeople()->first()->getPerson()->getFullname();
            //         },
            //         'placeholder' => 'placeholder.support',
            //         'required' => false,
            // ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Rdv::class,
            'translation_domain' => 'forms',
        ]);
    }
}
