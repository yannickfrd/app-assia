<?php

namespace App\Form\Support;

use App\Form\Avdl\AvdlType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class SupportGroupAvdlType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->remove('status')
            // ->remove('startDate')
            // ->remove('endDate')
            ->remove('endStatusComment')
            ->remove('theoreticalEndDate')
            ->add('avdl', AvdlType::class);
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
