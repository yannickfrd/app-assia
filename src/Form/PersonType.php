<?php

namespace App\Form;

use App\Entity\Person;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

class PersonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add("firstname")
        ->add("lastname")
        ->add("birthdate", DateType::class, [
            "widget" => "single_text"
        ])
        ->add("sex")
        ->add("comment")
        // ->add("creationDate", DateTimeType::class, [
        //     "widget" => "single_text",
        //     'format' => 'dd/MM/YYY H:m',
        // ])                    
        // ->add("updateDate", DateTimeType::class, [
        //     "widget" => "single_text",
        //     'format' => 'dd/MM/YYY H:m',
        // ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Person::class,
        ]);
    }
}
