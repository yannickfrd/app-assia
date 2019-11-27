<?php

namespace App\Form;

use App\Entity\SitBudgetGrp;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;

class SitBudgetGrpType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            // ->add("ressourcesGrpAmt", MoneyType::class, [
            //     "attr" => ["class" => "text-right"],
            //     "required" => false
            // ])
            // ->add("chargesGrpAmt", MoneyType::class, [
            //     "attr" => ["class" => "text-right"],
            //     "required" => false
            // ])
            // ->add("debtsGrpAmt", MoneyType::class, [
            //     "attr" => ["class" => "text-right"],
            //     "required" => false
            // ])
            // ->add("monthlyRepaymentAmt", MoneyType::class, [
            //     "attr" => ["class" => "text-right"],
            //     "required" => false
            // ])
            ->add("taxIncomeN1Amt", MoneyType::class, [
                "attr" => ["class" => "text-right"],
                "required" => false
            ])
            ->add("taxIncomeN2Amt", MoneyType::class, [
                "attr" => ["class" => "text-right"],
                "required" => false
            ])
            ->add("commentSitBudget");
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            "data_class" => SitBudgetGrp::class,
            "translation_domain" => "sitBudget"
        ]);
    }
}
