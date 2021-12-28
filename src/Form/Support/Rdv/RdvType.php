<?php

namespace App\Form\Support\Rdv;

use App\Entity\Support\Rdv;
use App\Form\Utils\Choices;
use App\Service\Api\GoogleApi\GoogleCalendarApiService;
use App\Service\Api\OutlookApi\OutlookCalendarApiService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RdvType extends AbstractType
{

    /** @var GoogleCalendarApiService */
    private $googleCalendarApiService;

    /** @var OutlookCalendarApiService */
    private $outlookCalendarApiService;

    public function __construct(GoogleCalendarApiService $googleCalendarApiService, OutlookCalendarApiService $outlookCalendarApiService)
    {
        $this->outlookCalendarApiService = $outlookCalendarApiService;
        $this->googleCalendarApiService = $googleCalendarApiService;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', null, [
                'attr' => [
                    'class' => 'font-weight-bold',
                    'placeholder' => 'rdv.placeholder.title',
                ],
            ])
            ->add('start', DateTimeType::class, [
                'widget' => 'single_text',
            ])
            ->add('end', DateTimeType::class, [
                'widget' => 'single_text',
            ])
            ->add('status', ChoiceType::class, [
                'choices' => Choices::getchoices(Rdv::STATUS),
                'placeholder' => 'placeholder.status',
                'required' => false,
            ])
            ->add('location', null, [
                'attr' => [
                    'class' => 'js-search',
                    'placeholder' => 'rdv.placeholder.location',
                    'autocomplete' => 'off',
                ],
            ])
            ->add('content', null, [
                'attr' => [
                    // "class" => "d-none",
                    'rows' => 5,
                    'placeholder' => 'rdv.placeholder.content',
                ],
            ])
            ->add('googleCalendar', CheckboxType::class, [
                'label' => 'Envoyer sur Google Agenda.',
                'label_attr' => [
                    'class' => 'custom-control-label'
                ],
                'attr'=> [
                    'class' => 'custom-control-input checkbox'
                ],
                'required' => false,
                'mapped' => false,
//                'data' => $this->gapi->getOnSessionIsChecked()// Regarde en session, si le user a déjà ckecké cette option
                'data' => $this->googleCalendarApiService->optionOnSessionIsChecked()// Regarde en session, si le user a déjà ckecké cette option
            ])
            ->add('outlookCalendar', CheckboxType::class, [
                'label' => 'Envoyer sur Outlook Agenda.',
                'label_attr' => [
                    'class' => 'custom-control-label'
                ],
                'attr'=> [
                    'class' => 'custom-control-input checkbox'
                ],
                'required' => false,
                'mapped' => false,
                'data' => $this->outlookCalendarApiService->optionOnSessionIsChecked()// Regarde en session, si le user a déjà ckecké cette option
            ])
        ;
        // ->add('user', EntityType::class, [
            //     'class' => User::class,
            //     'choice_label' => 'fullname',
            //         'query_builder' => function (UserRepository $repo) {
            //             return $repo->getUsersForCalendarQueryBuilder($this->security->getUser());
            //         },
            //     'placeholder' => 'placeholder.user',
            //     'required' => true,
            // ])
            // ->add('supportGroup', EntityType::class, [
            //         'class' => SupportGroup::class,
            //         'choices' => $this->supportGroupRepo->getSupportsOfUserQueryBuilder($this->security->getUser()),
            //         'choice_label' => function (SupportGroup $supportGroup) {
            //             return $supportGroup->getSupportPeople()->first()->getPerson()->getFullname();
            //         },
            //         'placeholder' => 'placeholder.support',
            //         'required' => false,
            // ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Rdv::class,
            'translation_domain' => 'forms',
        ]);
    }
}
