<?php

declare(strict_types=1);

namespace App\Command\Event;

use App\Entity\Event\Alert;
use App\Entity\Event\Rdv;
use App\Entity\Organization\User;
use App\Repository\Organization\UserRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;

class SendRdvAlertsCommand extends Command
{
    protected static $defaultName = 'app:rdv:send-rdv-alerts';
    protected static $defaultDescription = 'Send email about rdv alerts to users.';

    protected $em;
    protected $userRepo;
    protected $mailer;

    /** @var SymfonyStyle */
    protected $io;

    protected $nbEmails = 0;

    public function __construct(EntityManagerInterface $em, UserRepository $userRepo, MailerInterface $mailer)
    {
        parent::__construct();

        $this->em = $em;
        $this->userRepo = $userRepo;
        $this->mailer = $mailer;
    }

    protected function configure(): void
    {
        $this->setDescription(self::$defaultDescription);
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);

        /** @var Collection<User> $users */
        $users = $this->userRepo->getUsersWithRdvAlerts(new \DateTime());

        $this->io->progressStart(count($users));

        foreach ($users as $user) {
            $nbUserAlerts = 0;

            foreach ($user->getRdvs() as $rdv) {
                foreach ($rdv->getAlerts() as $alert) {
                    if (Alert::EMAIL_TYPE !== $alert->getType()) {
                        continue;
                    }

                    $this->sendEmail($user, $rdv);

                    $alert->setSended(true);
                    ++$nbUserAlerts;
                }
            }

            $this->io->progressAdvance();
        }

        $this->io->progressFinish();

        $this->em->flush();

        $this->io->success("$this->nbEmails emails were sent!");

        return Command::SUCCESS;
    }

    protected function sendEmail(User $user, Rdv $rdv): void
    {
        try {
            $email = (new TemplatedEmail())
                ->to($user->getEmail())
                ->subject('Application Assia | Rappel rendez-vous : '.$rdv->getTitle())
                ->htmlTemplate('emails/rdv_alert_email.html.twig')
                ->context([
                    'user' => $user,
                    'rdv' => $rdv,
                ])
            ;

            $this->mailer->send($email);

            ++$this->nbEmails;
        } catch (TransportExceptionInterface $e) {
            $this->io->error($e);
        }
    }
}
