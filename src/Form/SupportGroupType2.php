<?php

namespace App\Form;

use App\Entity\Person;
use App\Entity\Service;

use App\Entity\SupportGroup;
use App\Entity\GroupPeople;
use App\Form\SitSocialType;
use App\Form\Utils\Choices;
use App\Form\SitHousingType;

use App\Form\SupportSitType;

use App\Form\SupportPersonType;

use App\Form\SitBudgetGroupType;
use App\Form\SitFamilyGroupType;
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


class SupportGroupType2 extends AbstractType
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
            ->add("supportPerson", CollectionType::class, [
                "entry_type"   => SupportPersonType::class,
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
            "data_class" => SupportGroup::class,
            "translation_domain" => "forms"
        ]);
    }
}
