<?php

namespace App\Notification;

use App\Entity\Organization\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Security\Core\Security;

class MailNotifier
{
    protected $mailer;
    protected $security;
    protected $appVersion;
    protected $adminEmail;

    public function __construct(MailerInterface $mailer, Security $security, string $appVersion = 'prod', string $adminEmail)
    {
        $this->security = $security;
        $this->mailer = $mailer;
        $this->appVersion = $appVersion;
        $this->adminEmail = $adminEmail;
    }

    public function send(TemplatedEmail $email): bool
    {
        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $e) {
            throw $e;

            return false;
        }

        return true;
    }

    public function getAppVersion(): string
    {
        return $this->appVersion;
    }

    public function getAdminEmail(): string
    {
        return $this->adminEmail;
    }

    public function getUser(): User
    {
        return $this->security->getUser();
    }
}
