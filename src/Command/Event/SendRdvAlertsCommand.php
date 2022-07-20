<?php

declare(strict_types=1);

namespace App\Command\Event;

use App\Entity\Event\Alert;
use App\Entity\Event\Rdv;
use App\Entity\Organization\User;
use App\Repository\Organization\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;

#[AsCommand(
    name: 'app:rdv:send-rdv-alerts',
    description: 'Send email about rdv alerts to users.',
)]
class SendRdvAlertsCommand extends Command
{
    protected EntityManagerInterface $em;
    protected UserRepository $userRepo;
    protected MailerInterface $mailer;

    protected SymfonyStyle $io;

    protected int $nbEmails = 0;

    public function __construct(EntityManagerInterface $em, UserRepository $userRepo, MailerInterface $mailer)
    {
        parent::__construct();

        $this->em = $em;
        $this->userRepo = $userRepo;
        $this->mailer = $mailer;
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);

        $users = $this->userRepo->getUsersWithRdvAlerts(new \DateTime());

        $this->io->progressStart(count($users));

        foreach ($users as $user) {
            foreach ($user->getRdvs() as $rdv) {
                foreach ($rdv->getAlerts() as $alert) {
                    if (Alert::EMAIL_TYPE !== $alert->getType()) {
                        continue;
                    }

                    $this->sendEmail($user, $rdv);

                    $alert->setSent(true);
                }
            }

            $this->io->progressAdvance();
        }

        $this->io->progressFinish();

        $this->em->flush();

        $this->io->success("{$this->nbEmails} emails were sent!");

        return Command::SUCCESS;
    }

    protected function sendEmail(User $user, Rdv $rdv): void
    {
        try {
            $email = (new TemplatedEmail())
                ->to($user->getEmail())
                ->subject('Application Assia | Rappel rendez-vous : '.$rdv->getTitle())
                ->htmlTemplate('emails/email_rdv_alert.html.twig')
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
