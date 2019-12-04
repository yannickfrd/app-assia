<?php

namespace App\Form;

use App\Entity\Person;
use App\Entity\Service;

use App\Entity\SupportGrp;
use App\Entity\GroupPeople;
use App\Form\SitSocialType;
use App\Form\Utils\Choices;
use App\Form\SitHousingType;

use App\Form\SupportSitType;

use App\Form\SupportPersType;

use App\Form\SitBudgetGrpType;
use App\Form\SitFamilyGrpType;
use App\Security\CurrentUserService;
use App\Repository\PersonRepository;
use App\Repository\ServiceRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;


class SupportGrpType2 extends AbstractType
{
    private $currentUser;
    private $groupPeople;

    public function __construct(CurrentUserService $currentUser)
    {
        $this->currentUser = $currentUser;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add("supportPers", CollectionType::class, [
                "entry_type"   => SupportPersType::class,
                "label" => false,
                "allow_add" => true,
                "allow_delete" => true,
                "delete_empty" => true,
                "required" => false
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            "data_class" => SupportGrp::class,
            "translation_domain" => "forms"
        ]);
    }
}
