<?php

namespace App\Command\Event;

use App\Entity\Event\Alert;
use App\Entity\Organization\User;
use App\Repository\Organization\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;

class SendTaskAlertsCommand extends Command
{
    protected static $defaultName = 'app:task:send-task-alerts';
    protected static $defaultDescription = 'Send email about task alerts to users.';

    protected $em;
    protected $userRepo;
    protected $mailer;

    /** @var SymfonyStyle */
    protected $io;

    protected string $notifType = '';
    protected int $nbEmails = 0;

    public function __construct(EntityManagerInterface $em, UserRepository $userRepo, MailerInterface $mailer)
    {
        parent::__construct();

        $this->em = $em;
        $this->userRepo = $userRepo;
        $this->mailer = $mailer;
    }

    protected function configure(): void
    {
        $this
            ->setDescription(self::$defaultDescription)
            ->addArgument('notif-type', InputArgument::REQUIRED,
                'Type of notification : daily-alerts, weekly-alerts or a custom delay (example: "+5 hours")')
            ->addOption('flush', 'f', InputArgument::OPTIONAL, 'Flush all modifications to alerts and tasks', true)
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
        $this->notifType = $input->getArgument('notif-type');
        $flushOption = (bool) $input->getOption('flush');

        $users = $this->userRepo->getUsersWithTaskAlerts((new \DateTime())->modify($this->getDelay()));

        $this->io->progressStart(count($users));

        foreach ($users as $user) {
            $nbUserAlerts = 0;

            $userSetting = $user->getSetting();
            if ($userSetting && ('daily-alerts' === $this->notifType && false === $userSetting->getDailyAlert()
                || 'weekly-alerts' === $this->notifType && false === $userSetting->getWeeklyAlert())) {
                continue;
            }

            $alertsGroups = [];
            foreach ($user->getTasks() as $task) {
                foreach ($task->getAlerts() as $alert) {
                    if (Alert::EMAIL_TYPE !== $alert->getType()) {
                        continue;
                    }

                    $support = $task->getSupportGroup();
                    if ($support) {
                        $alertsGroups[$support->getId()]['name'] = $support->getHeader()->getFullname();
                        $alertsGroups[$support->getId()]['alerts'][$task->getId()] = $alert;
                    } else {
                        $alertsGroups[0]['name'] = 'Non-défini';
                        $alertsGroups[0]['alerts'][$task->getId()] = $alert;
                    }
                    $alert->setSended(true);
                    ++$nbUserAlerts;
                }
            }

            if ($nbUserAlerts > 0) {
                $this->sendEmail($user, $alertsGroups, $nbUserAlerts);
            }

            $this->io->progressAdvance();
        }

        $this->io->progressFinish();

        if ($flushOption) {
            $this->em->flush();
        }

        $this->io->success("{$this->nbEmails} emails were sent!");

        return Command::SUCCESS;
    }

    protected function sendEmail(User $user, array $alertsGroups, int $nbUserAlerts): void
    {
        try {
            $email = (new TemplatedEmail())
                ->to($user->getEmail())
                ->subject('Application Assia | '.$this->getEmailSubject())
                ->htmlTemplate('emails/task_alert_email.html.twig')
                ->context([
                    'user' => $user,
                    'alerts_groups' => $alertsGroups,
                    'nb_user_alerts' => $nbUserAlerts,
                    'notif_type' => $this->notifType,
                ])
            ;

            $this->mailer->send($email);

            ++$this->nbEmails;
        } catch (TransportExceptionInterface $e) {
            $this->io->error($e);
        }
    }

    protected function getDelay(): string
    {
        switch ($this->notifType) {
            case 'weekly-alerts':
                return '+ 1 week';
            case 'daily-alerts':
                return '+ 1 day';
            default:
                return $this->notifType;
        }
    }

    protected function getEmailSubject(): string
    {
        switch ($this->notifType) {
            case 'weekly-alerts':
                return 'Vos rappels de la semaine';
            case 'daily-alerts':
                return 'Vos rappels du jour';
            default:
                return 'Vous avez des rappels';
        }
    }
}
