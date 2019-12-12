<?php

namespace App\Form\Support;

use App\Entity\SupportPerson;

use App\Form\Support\Evaluation\SitAdmType;
use App\Form\Support\Evaluation\SitProfType;
use App\Form\Support\Evaluation\SitBudgetType;
use App\Form\Support\Evaluation\SitFamilyPersonType;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SupportSitType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("sitAdm", SitAdmType::class)
            ->add("sitFamilyPerson", SitFamilyPersonType::class)
            ->add("sitProf", SitProfType::class)
            ->add("sitBudget", SitBudgetType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            "data_class" => SupportPerson::class,
        ]);
    }
}
