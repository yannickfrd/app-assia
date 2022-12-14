<?php

namespace App\Form\Event;

use App\Entity\Event\Rdv;
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
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Constraints\Count;

class RdvType extends AbstractType
{
    private $tagRepo;
    private $user;
    private $supportGroupRepo;

    public function __construct(
        TagRepository $tagRepo,
        Security $security,
        SupportGroupRepository $supportGroupRepo
    ) {
        $this->tagRepo = $tagRepo;
        $this->user = $security->getUser();
        $this->supportGroupRepo = $supportGroupRepo;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Rdv $rdv */
        $rdv = $options['data'];
        /** @var SupportGroup $supportGroup */
        $supportGroup = $options['support_group'] ?? $options['data']->getSupportGroup();
        $service = $supportGroup ? $supportGroup->getService() : null;

        $builder
            ->add('title', null, [
                'attr' => [
                    'class' => 'fw-bold',
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
                'choices' => Choices::getChoices(Rdv::STATUS),
                'placeholder' => 'placeholder.status',
                'required' => false,
            ])
            ->add('location', null, [
                'attr' => [
                    'placeholder' => 'rdv.placeholder.location',
                    'autocomplete' => 'off',
                ],
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
                'constraints' => [
                    new Count(['min' => 1]),
                ],
            ])
            ->add('supportGroup', EntityType::class, [
                'class' => SupportGroup::class,
                'choices' => $this->supportGroupRepo->getSupportsOfUser($this->user, $supportGroup),
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
            ->add('content', null, [
                'attr' => [
                    'rows' => 3,
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
                    'placeholder' => 'placeholder.tags',
                    'size' => 1,
                ],
                'required' => false,
            ])
            ->add('alerts', CollectionType::class, [
                'entry_type' => AlertType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'delete_empty' => true,
                'prototype' => true,
                'by_reference' => false,
            ])
            ->add('_googleCalendar', CheckboxType::class, [
                'label' => 'rdv.label.google',
                'attr' => ['class' => 'api-calendar'],
                'mapped' => false,
                'required' => false,
            ])
            ->add('_outlookCalendar', CheckboxType::class, [
                'label' => 'rdv.label.outlook',
                'attr' => ['class' => 'api-calendar'],
                'mapped' => false,
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Rdv::class,
            'translation_domain' => 'forms',
            'support_group' => null,
        ]);
    }
}
