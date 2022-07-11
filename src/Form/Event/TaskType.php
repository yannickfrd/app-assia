<?php

namespace App\Form\Event;

use App\Entity\Event\Task;
use App\Entity\Organization\Tag;
use App\Entity\Organization\User;
use App\Entity\Support\SupportGroup;
use App\Entity\Support\SupportPerson;
use App\Form\Utils\Choices;
use App\Repository\Organization\TagRepository;
use App\Repository\Organization\UserRepository;
use App\Repository\Support\SupportGroupRepository;
use App\Repository\Support\SupportPersonRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;

class TaskType extends AbstractType
{
    private $supportGroupRepo;
    private $security;
    private $tagRepo;

    public function __construct(SupportGroupRepository $supportGroupRepo, Security $security, TagRepository $tagRepo)
    {
        $this->supportGroupRepo = $supportGroupRepo;
        $this->security = $security;
        $this->tagRepo = $tagRepo;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var SupportGroup $supportGroup */
        $supportGroup = $options['support_group'] ?? $options['data']->getSupportGroup();
        $service = $supportGroup ? $supportGroup->getService() : null;

        /** @var User $user */
        $user = $this->security->getUser();

        $builder
            ->add('title', null, [
                'attr' => [
                    'class' => 'fw-bold',
                    'placeholder' => 'task.title.placeholder',
                ],
            ])
            ->add('status', CheckboxType::class, [
                'required' => false,
            ])
            ->add('end', DateTimeType::class, [
                'widget' => 'single_text',
            ])
            ->add('_endDate', DateType::class, [
                'widget' => 'single_text',
                'mapped' => false,
            ])
            ->add('_endTime', TimeType::class, [
                'widget' => 'single_text',
                'mapped' => false,
            ])
            ->add('level', ChoiceType::class, [
                'choices' => Choices::getChoices(Task::LEVEL),
                'attr' => ['data-default-level' => Task::MEDIUM_LEVEL],
                'placeholder' => 'task.level.placeholder',
                'required' => false,
            ])
             ->add('users', EntityType::class, [
                'class' => User::class,
                'query_builder' => function (UserRepository $userRepo) use ($service, $user) {
                    return $userRepo->findUsersOfCurrentUserQueryBuilder($user, $service);
                },
                'choice_label' => 'fullname',
                'multiple' => true,
                'label' => 'event.users',
                'attr' => [
                    'placeholder' => 'event.users.placeholder',
                    'size' => 1,
                ],
            ])
            ->add('supportGroup', EntityType::class, [
                'class' => SupportGroup::class,
                'choices' => $this->supportGroupRepo->getSupportsOfUser($user, $supportGroup),
                'choice_label' => function (SupportGroup $supportGroup) {
                    return $supportGroup->getHeader()->getFullname();
                },
                'label' => 'event.support_group',
                'label_attr' => [
                    'class' => 'col-6 col-md-6',
                ],
                'placeholder' => 'event.support_group.placeholder',
                'required' => false,
            ])
            ->add('location', null, [
                'attr' => [
                    'class' => 'js-search',
                    'placeholder' => 'event.location.placeholder',
                    'autocomplete' => 'off',
                ],
            ])
            ->add('tags', EntityType::class, [
                'class' => Tag::class,
                'label' => false,
                'multiple' => true,
                'expanded' => false,
                'by_reference' => false,
                'choices' => $service ?
                    $this->tagRepo->getTagsByService($service, 'task') :
                    $this->tagRepo->findAllTags('task'),
                'choice_label' => 'name',
                'attr' => [
                    'class' => 'multi-select',
                    'placeholder' => 'placeholder.tags',
                    'size' => 1,
                ],
                'required' => false,
            ])
            ->add('content', null, [
                'attr' => [
                    'rows' => 3,
                    'placeholder' => 'event.content.placeholder',
                ],
            ])
            // ->add('supportPeople', EntityType::class, [
            //     'class' => SupportPerson::class,
            //     'choices' => [],
            //     'choice_label' => function (SupportPerson $supportPerson) {
            //         return $supportPerson->getPerson()->getFullname();
            //     },
            //     'multiple' => true,
            //     'label' => 'event.support_people',
            //     'attr' => [
            //         'class' => 'event-input-select-person d-none',
            //         'placeholder' => 'event.support_people.placeholder',
            //         'size' => 1,
            //     ],
            //     'required' => false,
            // ])
            ->add('alerts', CollectionType::class, [
                'entry_type' => AlertType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'delete_empty' => true,
                'prototype' => true,
                'by_reference' => false,
            ])

            // ->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $formEvent) use ($supportGroup) {
            //     $this->postSetDataForm($formEvent, $supportGroup);
            // })
            // ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $formEvent) {
            //     $this->preSubmitForm($formEvent);
            // })
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Task::class,
            'translation_domain' => 'forms',
            'support_group' => null,
        ]);
    }

    private function postSetDataForm(FormEvent $formEvent, ?SupportGroup $supportGroup): void
    {
        if (!$supportGroup) {
            return;
        }

        $formEvent->getForm()
            ->add('supportPeople', EntityType::class, [
                'class' => SupportPerson::class,
                'choices' => $supportGroup->getSupportPeople(),
                'choice_label' => function (SupportPerson $supportPerson) {
                    return $supportPerson->getPerson()->getFullname();
                },
                'multiple' => true,
                'attr' => ['size' => 1],
                'required' => false,
            ])
        ;
    }

    private function preSubmitForm(FormEvent $formEvent): void
    {
        $supportGroupId = null;

        if (isset($formEvent->getData()['supportGroup'])) {
            $supportGroupId = $formEvent->getData()['supportGroup'];
        }

        if (isset($formEvent->getData()['supportPeople'])) {
            $supportPeopleId = $formEvent->getData()['supportPeople'];
            $formEvent->getForm()->get('supportPeople')->setData($supportPeopleId);
        }

        if (!$supportGroupId) {
            return;
        }

        $formEvent->getForm()
            ->add('supportPeople', EntityType::class, [
                'class' => SupportPerson::class,
                'query_builder' => function (SupportPersonRepository $supportPersonRepos) use ($supportGroupId) {
                    return $supportPersonRepos->findPeopleInSupportByIdqueryBuilder($supportGroupId);
                },
                'multiple' => true,
                'required' => false,
            ])
        ;
    }
}
