<?php

namespace App\Form\Support\Rdv;

use App\Entity\Organization\Tag;
use App\Entity\Organization\User;
use App\Entity\Support\Rdv;
use App\Entity\Support\SupportGroup;
use App\Form\Utils\Choices;
use App\Repository\Organization\TagRepository;
use App\Repository\Organization\UserRepository;
use App\Repository\Support\SupportGroupRepository;
use App\Security\CurrentUserService;
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
    private $currentUser;
    private $supportGroupRepo;

    public function __construct(
        TagRepository $tagRepo,
        CurrentUserService $currentUser,
        SupportGroupRepository $supportGroupRepo
    ) {
        $this->tagRepo = $tagRepo;
        $this->currentUser = $currentUser;
        $this->supportGroupRepo = $supportGroupRepo;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Rdv $rdv */
        $rdv = $options['data'];
        /** @var SupportGroup $supportGroup */
        $supportGroup = $options['support_group'] ?? $options['data']->getSupportGroup();
        $service = $supportGroup ? $supportGroup->getService() : null;
        $user = $this->currentUser->getUser();

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
