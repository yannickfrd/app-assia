<?php

namespace App\Form\Support;

use App\Entity\Device;
use App\Entity\Service;
use App\Entity\SupportGroup;
use App\Entity\User;
use App\Form\Utils\Choices;
use App\Repository\DeviceRepository;
use App\Repository\ServiceRepository;
use App\Repository\UserRepository;
use App\Security\CurrentUserService;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SupportGroupType extends AbstractType
{
    private $currentUser;

    public function __construct(CurrentUserService $currentUser)
    {
        $this->currentUser = $currentUser;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $supportGroup = $options['data'];

        $builder
            ->add('service', EntityType::class, [
                'class' => Service::class,
                'choice_label' => 'name',
                'query_builder' => function (ServiceRepository $repo) {
                    return $repo->getServicesFromUserQueryList($this->currentUser);
                },
                'placeholder' => '-- Select --',
            ])
            ->add('device', EntityType::class, [
                'class' => Device::class,
                'choice_label' => 'name',
                'query_builder' => function (DeviceRepository $repo) {
                    return $repo->getDevicesFromUserQueryList($this->currentUser);
                },
                'placeholder' => '-- Select --',
            ])
            ->add('status', ChoiceType::class, [
                'choices' => Choices::getChoices(SupportGroup::STATUS),
                'placeholder' => '-- Select --',
            ])
            ->add('startDate', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('referent', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'fullname',
                'query_builder' => function (UserRepository $repo) use ($supportGroup) {
                    return $repo->getUsersQueryList($this->currentUser, $supportGroup->getReferent());
                },
                'placeholder' => '-- Select --',
            ])
            ->add('referent2', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'fullname',
                'query_builder' => function (UserRepository $repo) use ($supportGroup) {
                    return $repo->getUsersQueryList($this->currentUser, $supportGroup->getReferent2());
                },
                'placeholder' => '-- Select --',
                'required' => false,
            ])
            ->add('endDate', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('endStatus', ChoiceType::class, [
                'choices' => Choices::getChoices(SupportGroup::END_STATUS),
                'placeholder' => '-- Select --',
                'required' => false,
            ])
            ->add('endStatusComment')
            ->add('agreement', CheckboxType::class, [
                'required' => true,
                'label_attr' => [
                    'class' => 'custom-control-label',
                ],
                'attr' => [
                    'class' => 'custom-control-input checkbox',
                ],
            ])
            ->add('comment', null, [
                'attr' => [
                    'rows' => 5,
                    'placeholder' => 'Write a comment about the social support',
                ],
            ]);
        // ->add("initEvalGroup", InitEvalGroupType::class)
        // ->add("supportPerson", CollectionType::class, [
        //     "entry_type"   => SupportPersonInitEvalType::class,
        //     "allow_add"    => false,
        //     "allow_delete" => false,
        //     "required" => false
        // ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SupportGroup::class,
            'translation_domain' => 'forms',
        ]);
    }
}
