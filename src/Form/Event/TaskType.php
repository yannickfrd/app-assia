<?php

namespace App\Form\Event;

use App\Entity\Event\Task;
use App\Entity\Organization\Tag;
use App\Entity\Organization\User;
use App\Entity\Support\SupportGroup;
use App\Form\Utils\Choices;
use App\Repository\Organization\TagRepository;
use App\Repository\Organization\UserRepository;
use App\Repository\Support\SupportGroupRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;

class TaskType extends AbstractType
{
    private $user;
    private $supportGroupRepo;
    private $tagRepo;

    public function __construct(
        Security $security,
        SupportGroupRepository $supportGroupRepo,
        TagRepository $tagRepo
    ) {
        $this->user = $security->getUser();
        $this->supportGroupRepo = $supportGroupRepo;
        $this->tagRepo = $tagRepo;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Task $task */
        $task = $options['data'];
        /** @var SupportGroup $supportGroup */
        $supportGroup = $options['support_group'] ?? $task->getSupportGroup();
        $service = $supportGroup ? $supportGroup->getService() : null;

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
                'query_builder' => function (UserRepository $userRepo) use ($service) {
                    return $userRepo->findUsersOfCurrentUserQueryBuilder($this->user, $service);
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
                'choices' => $this->supportGroupRepo->getSupportsOfUser($this->user, $supportGroup),
                'choice_label' => function (SupportGroup $supportGroup) {
                    return $supportGroup->getHeader()->getFullname();
                },
                'label' => 'event.support_group',
                'placeholder' => 'event.support_group.placeholder',
                'required' => false,
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
            ->add('alerts', CollectionType::class, [
                'entry_type' => AlertType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'delete_empty' => true,
                'prototype' => true,
                'by_reference' => false,
            ])
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
}
