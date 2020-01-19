<?php

namespace App\Form\Support;

use App\Entity\SupportGroup;

use App\Form\Support\SupportPersonType;

use App\Security\CurrentUserService;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
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
                "label_attr" => [
                    "class" => "sr-only"
                ],
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
