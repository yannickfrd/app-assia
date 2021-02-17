<?php

namespace App\Form\Support\Rdv;

use App\Entity\Organization\User;
use App\Entity\Support\Rdv;
use App\Entity\Support\SupportGroup;
use App\Form\Utils\Choices;
use App\Repository\Organization\UserRepository;
use App\Repository\Support\SupportGroupRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;

class RdvType extends AbstractType
{
    private $security;
    private $repoSupportGroup;

    public function __construct(Security $security, SupportGroupRepository $repoSupportGroup)
    {
        $this->security = $security;
        $this->repoSupportGroup = $repoSupportGroup;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
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
                    // "class" => "d-none",
                    'rows' => 5,
                    'placeholder' => 'rdv.placeholder.content',
                ],
            ]);
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
            //         'choices' => $this->repoSupportGroup->getSupportsOfUserQueryBuilder($this->security->getUser()),
            //         'choice_label' => function (SupportGroup $supportGroup) {
            //             return $supportGroup->getSupportPeople()->first()->getPerson()->getFullname();
            //         },
            //         'placeholder' => 'placeholder.support',
            //         'required' => false,
            // ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Rdv::class,
            'translation_domain' => 'forms',
        ]);
    }
}
