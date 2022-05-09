<?php

namespace App\Command\Event;

use App\Repository\Event\RdvRepository;
use App\Service\DoctrineTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * TEMPORAIRE. A SUPPRIMER.
 */
class AddUserToRdvCommand extends Command
{
    use DoctrineTrait;

    protected static $defaultName = 'app:rdv:add-user';
    protected static $defaultDescription = 'Add users to rdv by created date.';

    private $em;
    private $rdvRepo;

    public function __construct(EntityManagerInterface $em, RdvRepository $rdvRepo)
    {
        parent::__construct();

        $this->em = $em;
        $this->rdvRepo = $rdvRepo;
    }

    protected function configure(): void
    {
        $this
            ->setDescription(self::$defaultDescription)
            ->addOption('flush', 'f', InputOption::VALUE_NONE,
                'Flush all modifications to rdvs')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $rdvs = $this->rdvRepo->findAll();

        $io->progressStart(count($rdvs) + ($input->getOption('flush') ? 1 : 0));

        foreach ($rdvs as $rdv) {
            if (0 === $rdv->getUsers()->count()) {
                $rdv->addUser($rdv->getCreatedBy());

                $io->progressAdvance();
            }
        }

        if ($input->getOption('flush')) {
            $this->disableListeners($this->em);

            $this->em->flush();

            $io->progressAdvance();
        }

        $io->progressFinish();

        $io->success('The Rdvs are updated !!');

        return Command::SUCCESS;
    }
}
