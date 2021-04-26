<?php

namespace App\Form\Admin;

use App\Form\Model\Support\SupportsByUserSearch;
use App\Form\Type\ServiceDeviceReferentSearchType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SupportsByUserSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('service', ServiceDeviceReferentSearchType::class, [
                'data_class' => SupportsByUserSearch::class,
                'data' => $builder->getData(),
            ])
            ->add('send', HiddenType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SupportsByUserSearch::class,
            'method' => 'get',
            'translation_domain' => 'forms',
            'csrf_protection' => false,
            'allow_extra_fields' => true,
        ]);
    }

    public function getBlockPrefix()
    {
        return '';
    }
}
