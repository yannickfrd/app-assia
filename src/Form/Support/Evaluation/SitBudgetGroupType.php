<?php

namespace App\Form\Support\Evaluation;

use App\Entity\SitBudgetGroup;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;

class SitBudgetGroupType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            // ->add("ressourcesGroupAmt", MoneyType::class, [
            //     "attr" => ["class" => "text-right"],
            //     "required" => false
            // ])
            // ->add("chargesGroupAmt", MoneyType::class, [
            //     "attr" => ["class" => "text-right"],
            //     "required" => false
            // ])
            // ->add("debtsGroupAmt", MoneyType::class, [
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
            "data_class" => SitBudgetGroup::class,
            "translation_domain" => "sitBudget"
        ]);
    }
}
