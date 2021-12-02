<?php

namespace App\Form\Support\Support;

use App\Entity\People\RolePerson;
use App\Entity\Support\SupportGroup;
use App\Repository\People\RolePersonRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddPersonToSupportType extends AbstractType
{
    protected $rolePersonRepo;

    public function __construct(RolePersonRepository $rolePersonRepo)
    {
        $this->rolePersonRepo = $rolePersonRepo;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var SupportGroup $supportGroup */
        $supportGroup = $options['attr']['supportGroup'];

        $builder
            ->add('rolePerson', EntityType::class, [
                'class' => RolePerson::class,
                'choice_label' => function (RolePerson $rolePerson) {
                    return $rolePerson->getPerson()->getFullname();
                },
                'choices' => $this->rolePersonRepo->findPeopleNotInSupport($supportGroup),
                'placeholder' => 'placeholder.person',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'forms',
        ]);
    }
}
