<?php

namespace App\Form\Support\Contribution;

use App\Entity\Organization\User;
use App\Entity\Support\Contribution;
use App\Form\Model\Support\ContributionSearch;
use App\Form\Type\DateSearchType;
use App\Form\Type\ServiceDeviceReferentSearchType;
use App\Form\Utils\Choices;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;

class ContributionSearchType extends AbstractType
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
            ->add('fullname', null, [
                'label_attr' => ['class' => 'sr-only'],
                'attr' => [
                    'placeholder' => 'search.fullname.placeholder',
                    'class' => 'w-max-180',
                ],
            ])
            ->add('type', ChoiceType::class, [
                'label_attr' => [
                    'class' => 'sr-only',
                ],
                'multiple' => true,
                'attr' => [
                    'class' => 'multi-select',
                    'data-select2-id' => 'contribution-type',
                ],
                'choices' => Choices::getchoices(Contribution::CONTRIBUTION_TYPE),
                'required' => false,
            ])
            ->add('dateType', ChoiceType::class, [
                'label_attr' => ['class' => 'sr-only'],
                'choices' => Choices::getChoices(ContributionSearch::DATE_TYPE),
                'placeholder' => 'placeholder.dateType',
                'required' => false,
            ])
            ->add('date', DateSearchType::class, [
                'data_class' => ContributionSearch::class,
            ])
            ->add('service', ServiceDeviceReferentSearchType::class, [
                'data_class' => ContributionSearch::class,
            ])
            ->add('export');
    }

    private function setFormData(FormBuilderInterface $builder): FormBuilderInterface
    {
        return $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            /** @var ContributionSearch */
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
            'data_class' => ContributionSearch::class,
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
