<?php

namespace App\Form\Support;

use App\Entity\SupportPerson;

use App\Form\Support\Evaluation\SitAdmPersonType;
use App\Form\Support\Evaluation\SitProfPersonType;
use App\Form\Support\Evaluation\SitBudgetPersonType;
use App\Form\Support\Evaluation\SitFamilyPersonType;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SupportSitType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("sitAdmPerson", SitAdmPersonType::class)
            ->add("sitFamilyPerson", SitFamilyPersonType::class)
            ->add("sitProfPerson", SitProfPersonType::class)
            ->add("sitBudgetPerson", SitBudgetPersonType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            "data_class" => SupportPerson::class,
        ]);
    }
}
