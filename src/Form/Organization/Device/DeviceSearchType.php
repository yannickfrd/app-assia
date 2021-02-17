<?php

namespace App\Form\Organization\Device;

use App\Entity\Organization\Pole;
use App\Entity\Organization\Service;
use App\Form\Model\Organization\DeviceSearch;
use App\Form\Utils\Choices;
use App\Repository\Organization\ServiceRepository;
use App\Security\CurrentUserService;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DeviceSearchType extends AbstractType
{
    private $currentUser;

    public function __construct(CurrentUserService $currentUser)
    {
        $this->currentUser = $currentUser;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', null, [
                'label_attr' => ['class' => 'sr-only'],
                'attr' => [
                    'class' => 'w-max-200',
                    'placeholder' => 'device.name',
                    'autocomplete' => 'off',
                ],
            ])
            ->add('service', EntityType::class, [
                'class' => Service::class,
                'choice_label' => 'name',
                'query_builder' => function (ServiceRepository $repo) {
                    return $repo->getServicesOfUserQueryBuilder($this->currentUser);
                },
                'label_attr' => ['class' => 'sr-only'],
                'placeholder' => 'placeholder.service',
                'required' => false,
            ])
            ->add('disabled', ChoiceType::class, [
                'label_attr' => ['class' => 'sr-only'],
                'choices' => Choices::getChoices(Choices::DISABLE),
                'placeholder' => 'placeholder.disabled',
                'required' => false,
            ]);

        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {
            $form = $event->getForm();

            if ($this->currentUser->hasRole('ROLE_SUPER_ADMIN')) {
                $form
                    ->add('pole', EntityType::class, [
                        'class' => Pole::class,
                        'choice_label' => 'name',
                        'label_attr' => ['class' => 'sr-only'],
                        'placeholder' => 'placeholder.pole',
                        'required' => false,
                ]);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => DeviceSearch::class,
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
