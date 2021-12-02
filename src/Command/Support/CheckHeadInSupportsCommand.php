<?php

namespace App\Command\Support;

use App\Repository\Support\SupportGroupRepository;
use App\Service\DoctrineTrait;
use App\Service\SupportGroup\SupportChecker;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Commande pour vérifier le demandeur principal dans chaque suivi.
 */
class CheckHeadInSupportsCommand extends Command
{
    use DoctrineTrait;

    protected static $defaultName = 'app:support:check_head';
    protected static $defaultDescription = 'Check head in supports';

    protected $supportGroupRepo;
    protected $em;
    protected $supportChecker;

    public function __construct(SupportGroupRepository $supportGroupRepo, EntityManagerInterface $em, SupportChecker $supportChecker)
    {
        $this->supportGroupRepo = $supportGroupRepo;
        $this->em = $em;
        $this->supportChecker = $supportChecker;
        $this->disableListeners($this->em);

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription(self::$defaultDescription)
            ->addOption('limit', 'l', InputOption::VALUE_OPTIONAL, 'Query limit', 1000)
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $limit = $input->getOption('limit');

        $supports = $this->supportGroupRepo->findBy([], ['updatedAt' => 'DESC'], $limit);
        $count = 0;

        foreach ($supports as $support) {
            $countHeads = 0;
            foreach ($support->getSupportPeople() as $supportPerson) {
                if ($supportPerson->getHead()) {
                    ++$countHeads;
                }
            }
            if (1 != $countHeads) {
                echo $support->getId()." => $countHeads DP\n";
                $this->supportChecker->checkValidHeader($support);
                ++$count;
            }
        }

        $this->em->flush();

        $io->success("The headers in support are checked !\n  ".$count.' / '.count($supports).' are invalids.');

        return Command::SUCCESS;
    }
}
