<?php

namespace App\EventListener;

use Twig\Environment;
use App\Event\EvaluationEditEvent;
use App\Notification\MailNotification;

class EvaluationEditListener
{
    protected $renderer;
    protected $notification;
    protected $evaluationListener;

    public function __construct(Environment $renderer, MailNotification $notification, bool $evaluationListener)
    {
        $this->notification = $notification;
        $this->renderer = $renderer;
        $this->evaluationListener = $evaluationListener;
    }

    public function onEdit(EvaluationEditEvent $event)
    {
        if (!$this->evaluationListener) {
            return;
        }

        $supportGroup = $event->getSupportGroup();
        $fullnamePerson = '';

        foreach ($supportGroup->getSupportPeople() as $supportPerson) {
            if ($supportPerson->getHead()) {
                $fullnamePerson = $supportPerson->getPerson()->getFullname();
            }
        }

        $htmlBody = $this->renderer->render(
            'emails/EvaluationEmail.html.twig',
            [
                'supportId' => $supportGroup->getId(),
                'fullnamePerson' => $fullnamePerson,
                'evaluation_group' => $event->getEvaluationGroup(),
                ]
            );

        $this->notification->send(
            ['email' => ('romain.madelaine@esperer-95.org'), 'name' => 'Admin'],
            'Esperer95.app | Evaluation enregistrée : '.$fullnamePerson.' ('.$supportGroup->getId().')',
            $htmlBody,
        );
    }
}
