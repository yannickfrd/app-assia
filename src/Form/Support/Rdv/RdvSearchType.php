<?php

namespace App\Form\Support\Rdv;

use App\Entity\Support\Rdv;
use App\Form\Utils\Choices;
use App\Form\Type\ServiceDeviceReferentSearchType;
use App\Entity\Organization\User;
use App\Form\Type\DateSearchType;
use App\Form\Model\Support\RdvSearch;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Security\Core\Security;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class RdvSearchType extends AbstractType
{
    /** @var User */
    private $user;

    public function __construct(Security $security)
    {
        $this->user = $security->getUser();
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->setFormData($builder);
        $builder
            ->add('id', null, [
                'label_attr' => ['class' => 'sr-only'],
                'attr' => [
                    'placeholder' => 'ID',
                    'class' => 'w-max-80',
                ],
            ])
            ->add('title', null, [
                'label_attr' => ['class' => 'sr-only'],
                'attr' => [
                    'placeholder' => 'Title',
                    'class' => 'w-max-170',
                ],
            ])
            ->add('fullname', null, [
                'label_attr' => ['class' => 'sr-only'],
                'attr' => [
                    'placeholder' => 'search.fullname.placeholder',
                    'class' => 'w-max-170',
                ],
            ])
            ->add('status', ChoiceType::class, [
                'multiple' => true,
                'choices' => Choices::getChoices(Rdv::STATUS),
                'attr' => [
                    'class' => 'multi-select',
                    'data-select2-id' => 'status',
                ],
                'placeholder' => 'placeholder.status',
                'required' => false,
            ])
            ->add('date', DateSearchType::class, [
                'data_class' => RdvSearch::class,
            ])
            ->add('service', ServiceDeviceReferentSearchType::class, [
                'data_class' => RdvSearch::class,
            ])
            ->add('export');
    }

    
    private function setFormData(FormBuilderInterface $builder): FormBuilderInterface
    {
        return $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            /** @var RdvSearch */
            $search = $event->getData();

            if (User::STATUS_SOCIAL_WORKER === $this->user->getStatus()) {
                $usersCollection = new ArrayCollection();
                $usersCollection->add($this->user);
                $search->setReferents($usersCollection);
            }
        });
    }
    
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => RdvSearch::class,
            'method' => 'get',
            'translation_domain' => 'forms',
            'allow_extra_fields' => true,
            'csrf_protection' => false,
        ]);
    }

    public function getBlockPrefix()
    {
        return '';
    }
}
