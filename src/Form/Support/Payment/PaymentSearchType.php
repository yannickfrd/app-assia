<?php

namespace App\Form\Support\Payment;

use App\Entity\Organization\User;
use App\Entity\Support\Payment;
use App\Form\Model\Support\PaymentSearch;
use App\Form\Type\DateSearchType;
use App\Form\Type\ServiceDeviceReferentSearchType;
use App\Form\Utils\Choices;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;

class PaymentSearchType extends AbstractType
{
    /** @var User */
    private $user;

    public function __construct(Security $security)
    {
        $this->user = $security->getUser();
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $this->setFormData($builder);
        $builder
            ->add('id', SearchType::class, [
                'label_attr' => ['class' => 'sr-only'],
                'attr' => [
                    'placeholder' => 'ID',
                    'class' => 'w-max-80',
                ],
                'required' => false,
            ])
            ->add('fullname', SearchType::class, [
                'label_attr' => ['class' => 'sr-only'],
                'attr' => [
                    'placeholder' => 'search.fullname.placeholder',
                    'class' => 'w-max-180',
                ],
                'required' => false,
            ])
            ->add('type', ChoiceType::class, [
                'label_attr' => ['class' => 'sr-only'],
                'multiple' => true,
                'attr' => [
                    'class' => 'multi-select',
                    'placeholder' => 'placeholder.type',
                    'size' => 1,
                ],
                'choices' => Choices::getChoices(Payment::TYPES),
                'required' => false,
            ])
            ->add('dateType', ChoiceType::class, [
                'label_attr' => ['class' => 'sr-only'],
                'choices' => Choices::getChoices(PaymentSearch::DATE_TYPE),
                'placeholder' => 'placeholder.dateType',
                'required' => false,
            ])
            ->add('date', DateSearchType::class, [
                'data_class' => PaymentSearch::class,
            ])
            ->add('service', ServiceDeviceReferentSearchType::class, [
                'data_class' => PaymentSearch::class,
            ])
            ->add('export');
    }

    private function setFormData(FormBuilderInterface $builder): FormBuilderInterface
    {
        return $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            /** @var PaymentSearch */
            $search = $event->getData();

            if (User::STATUS_SOCIAL_WORKER === $this->user->getStatus()) {
                $usersCollection = new ArrayCollection();
                $usersCollection->add($this->user);
                $search->setReferents($usersCollection);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PaymentSearch::class,
            'method' => 'get',
            'translation_domain' => 'forms',
            'allow_extra_fields' => true,
            'csrf_protection' => false,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}
