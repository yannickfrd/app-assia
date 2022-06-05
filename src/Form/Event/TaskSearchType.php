<?php

namespace App\Form\Event;

use App\Entity\Event\Task;
use App\Entity\Organization\Tag;
use App\Entity\Organization\User;
use App\Form\Model\Event\TaskSearch;
use App\Form\Type\DateSearchType;
use App\Form\Type\ServiceDeviceReferentSearchType;
use App\Form\Utils\Choices;
use App\Repository\Organization\TagRepository;
use App\Repository\Organization\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;

class TaskSearchType extends AbstractType
{
    /** @var User */
    private $user;

    private $tagRepo;

    public function __construct(Security $security, TagRepository $tagRepo)
    {
        $this->user = $security->getUser();
        $this->tagRepo = $tagRepo;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->setFormData($builder);

        $builder
            ->add('id', null, [
                'label_attr' => ['class' => 'sr-only'],
                'attr' => [
                    'placeholder' => 'ID',
                    'class' => 'w-max-80',
                ],
            ])
            ->add('title', null, [
                'label_attr' => ['class' => 'sr-only'],
                'attr' => [
                    'placeholder' => 'search',
                    'class' => 'w-max-170',
                ],
            ])
            ->add('fullname', null, [
                'label_attr' => ['class' => 'sr-only'],
                'attr' => [
                    'placeholder' => 'search.fullname.placeholder',
                    'class' => 'w-max-170',
                ],
            ])
            ->add('location', null, [
                'label_attr' => ['class' => 'sr-only'],
                'attr' => [
                    'placeholder' => 'Location',
                    'class' => 'w-max-170',
                ],
            ])
            ->add('level', ChoiceType::class, [
                'multiple' => true,
                'choices' => Choices::getChoices(Task::LEVEL),
                'attr' => [
                    'class' => 'multi-select w-min-140',
                    'size' => 1,
                    'placeholder' => 'task.level.placeholder',
                ],
                'required' => false,
            ])
            ->add('status', ChoiceType::class, [
                'multiple' => true,
                'choices' => Choices::getChoices(Task::STATUS),
                'attr' => [
                    'class' => 'multi-select w-min-160',
                    'placeholder' => 'placeholder.status',
                    'size' => 1,
                ],
                'required' => false,
            ])
            ->add('date', DateSearchType::class, [
                'data_class' => TaskSearch::class,
            ])
            ->add('service', ServiceDeviceReferentSearchType::class, [
                'data_class' => TaskSearch::class,
                'attr' => [
                    'options' => ['poles', 'services', 'subServices', 'devices'],
                ],
            ])
            ->add('users', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'fullname',
                'multiple' => true,
                'query_builder' => function (UserRepository $repo) {
                    return $repo->findUsersOfCurrentUserQueryBuilder($this->user);
                },
                'attr' => [
                    'class' => 'multi-select w-min-150 w-max-220',
                    'placeholder' => 'event.users.placeholder',
                    'size' => 1,
                ],
                'required' => false,
            ])
            ->add('tags', EntityType::class, [
                'class' => Tag::class,
                'multiple' => true,
                'expanded' => false,
                'by_reference' => false,
                'choices' => $this->tagRepo->getTagsByService($options['service'], 'event'),
                'choice_label' => 'name',
                'label_attr' => ['class' => 'sr-only'],
                'attr' => [
                    'class' => 'multi-select w-min-200 w-max-220',
                    'placeholder' => 'placeholder.tags',
                    'size' => 1,
                ],
                'required' => false,
            ])
            ->add('export')
        ;
    }

    private function setFormData(FormBuilderInterface $builder): FormBuilderInterface
    {
        return $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            /** @var TaskSearch $search */
            $search = $event->getData();

            if (User::STATUS_SOCIAL_WORKER === $this->user->getStatus()) {
                $usersCollection = new ArrayCollection([$this->user]);
                $search->setUsers($usersCollection);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => TaskSearch::class,
            'method' => 'get',
            'translation_domain' => 'forms',
            'allow_extra_fields' => true,
            'csrf_protection' => false,
            'service' => null,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}
