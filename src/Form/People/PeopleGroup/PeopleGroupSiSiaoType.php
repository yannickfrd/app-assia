<?php

namespace App\Form\People\PeopleGroup;

use App\Entity\People\PeopleGroup;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PeopleGroupSiSiaoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('siSiaoId', null, [
                'required' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PeopleGroup::class,
            'translation_domain' => 'forms',
        ]);
    }
}
