<?php

namespace App\Form\Support\Support;

use App\Entity\Organization\User;
use App\Repository\Organization\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;

class SwitchSupportReferentType extends AbstractType
{
    private $userRepo;
    /** @var User */
    private $user;

    public function __construct(UserRepository $userRepo, Security $security)
    {
        $this->userRepo = $userRepo;
        $this->user = $security->getUser();
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('_oldReferent', EntityType::class, $optionsReferent = $this->optionsReferent())
            ->add('_newReferent', EntityType::class, $optionsReferent)
            ->add('save', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'forms',
        ]);
    }

    public function getBlockPrefix(): string
    {
        return '';
    }

    private function optionsReferent(): array
    {
        return [
            'class' => User::class,
            'choice_label' => 'fullname',
            'choices' => $this->userRepo->findUsersOfCurrentUserQueryBuilder($this->user)
                ->getQuery()
                ->getResult(),
            'attr' => ['autocomplete' => true],
            'placeholder' => 'placeholder.select',
            'required' => true,
        ];
    }
}
