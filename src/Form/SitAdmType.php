<?php

namespace App\Form;

use App\Entity\SitAdm;
use App\Form\Utils\Choices;
use App\Form\Utils\SelectList;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class SitAdmType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("nationality", ChoiceType::class, [
                "choices" => Choices::getChoices(SitAdm::NATIONALITY),
                "placeholder" => "-- Select --",
                "required" => false
            ])
            ->add("country")
            ->add("paper", ChoiceType::class, [
                "choices" => Choices::getChoices(SelectList::YES_NO),
                "placeholder" => "-- Select --",
                "required" => false
            ])
            ->add("paperType", ChoiceType::class, [
                "choices" => Choices::getChoices(SitAdm::PAPER_TYPE),
                "placeholder" => "-- Select --",
                "required" => false
            ])
            ->add("rightReside", ChoiceType::class, [
                "choices" => Choices::getChoices(SitAdm::RIGHT_TO_RESIDE),
                "placeholder" => "-- Select --",
                "required" => false
            ])
            ->add("applResidPermit", ChoiceType::class, [
                "choices" => Choices::getChoices(SelectList::YES_NO),
                "placeholder" => "-- Select --",
                "required" => false
            ])
            ->add("endDateValidPermit", DateType::class, [
                "widget" => "single_text",
                "attr" => [
                    "placeholder" => "jj/mm/aaaa",
                ],
                "required" => false
            ])
            ->add("renewalDatePermit", DateType::class, [
                "widget" => "single_text",
                "attr" => [
                    "placeholder" => "jj/mm/aaaa",
                ],
                "required" => false
            ])
            ->add("nbRenewals")
            ->add("noRightsOpen")
            ->add("rightWork")
            ->add("rightSocialBenf")
            ->add("housingAlw")
            ->add("rightSocialSecu", ChoiceType::class, [
                "choices" => Choices::getChoices(SelectList::YES_NO),
                "placeholder" => "-- Select --",
                "required" => false
            ])
            ->add("socialSecu", ChoiceType::class, [
                "choices" => Choices::getChoices(SitAdm::SOCIAL_SECURITY),
                "placeholder" => "-- Select --",
                "required" => false
            ])
            ->add("socialSecuOffice")
            ->add("commentSitAdm", null, [
                "label_attr" => ["class" => "col-sm-12"],
                "attr" => [
                    "rows" => 5,
                    "placeholder" => "Write a comment about the administrative situation"
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            "data_class" => SitAdm::class,
            "translation_domain" => "sitAdm",
        ]);
    }
}
