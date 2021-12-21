<?php

namespace App\Form\Support\Note;

use App\Entity\Organization\Tag;
use App\Entity\Organization\User;
use App\Entity\Support\Note;
use App\Form\Model\Support\NoteSearch;
use App\Form\Type\DateSearchType;
use App\Form\Type\ServiceDeviceReferentSearchType;
use App\Form\Utils\Choices;
use App\Repository\Organization\TagRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;

class NoteSearchType extends AbstractType
{
    /** @var User */
    private $user;

    /** @var TagRepository */
    private $tagRepo;

    public function __construct(Security $security, TagRepository $tagRepo)
    {
        $this->user = $security->getUser();
        $this->tagRepo = $tagRepo;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
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
            ->add('content', null, [
                'label_attr' => ['class' => 'sr-only'],
                'attr' => [
                    'placeholder' => 'Search',
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
            ->add('type', ChoiceType::class, [
                'label_attr' => ['class' => 'sr-only'],
                'choices' => Choices::getchoices(Note::TYPE),
                'attr' => [
                    'class' => 'w-max-160',
                ],
                'placeholder' => 'placeholder.type',
                'required' => false,
            ])
            ->add('status', ChoiceType::class, [
                'label_attr' => [
                    'class' => 'sr-only',
                ],
                'choices' => Choices::getchoices(Note::STATUS),
                'attr' => [
                    'class' => 'w-max-160',
                ],
                'placeholder' => 'placeholder.status',
                'required' => false,
            ])
            ->add('date', DateSearchType::class, [
                'data_class' => NoteSearch::class,
            ])
            ->add('service', ServiceDeviceReferentSearchType::class, [
                'data_class' => NoteSearch::class,
            ])
            ->add('tags', EntityType::class, [
                'class' => Tag::class,
                'multiple' => true,
                'expanded' => false,
                'required' => false,
                'by_reference' => false,
                'choices' => $this->tagRepo->findAllWithPartialLoadGetResult(),
                'choice_label' => 'name',
                'label_attr' => ['class' => 'sr-only'],
                'attr' => [
                    'class' => 'multi-select w-min-160 w-max-180',
                    'data-select2-id' => 'search-tags',
                    'size' => 1,
                ],
            ])
        ;
    }

    private function setFormData(FormBuilderInterface $builder): FormBuilderInterface
    {
        return $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            /** @var NoteSearch */
            $search = $event->getData();

            if (User::STATUS_SOCIAL_WORKER === $this->user->getStatus()) {
                $usersCollection = new ArrayCollection();
                $usersCollection->add($this->user);
                $search->setReferents($usersCollection);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => NoteSearch::class,
            'method' => 'get',
            'translation_domain' => 'forms',
            'allow_extra_fields' => true,
            'csrf_protection' => false,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}
