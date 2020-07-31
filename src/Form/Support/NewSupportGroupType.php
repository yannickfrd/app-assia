<?php

namespace App\Form\Support;

use App\Security\CurrentUserService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class NewSupportGroupType extends AbstractType
{
    protected $currentUser;

    public function __construct(CurrentUserService $currentUser)
    {
        $this->currentUser = $currentUser;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->remove('originRequest')
            ->remove('referent2')
            ->remove('theoreticalEndDate')
            ->remove('startDate')
            ->remove('endDate')
            ->remove('endStatus')
            ->remove('endAccommodation')
            ->remove('endStatusComment')
            ->remove('agreement')
            ->remove('supportPeople')
            ->remove('location')
            ->remove('comment');
    }

    public function getParent()
    {
        return SupportGroupType::class;
    }

    public function getBlockPrefix()
    {
        return 'support';
    }
}
