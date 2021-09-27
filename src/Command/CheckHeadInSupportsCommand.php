<?php

namespace App\Command;

use App\Repository\Support\SupportGroupRepository;
use App\Service\DoctrineTrait;
use App\Service\SupportGroup\SupportChecker;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Commande pour vérifier le demandeur principal dans chaque suivi.
 */
class CheckHeadInSupportsCommand extends Command
{
    use DoctrineTrait;

    protected static $defaultName = 'app:support:check_head';

    protected $repo;
    protected $manager;
    protected $supportChecker;

    public function __construct(SupportGroupRepository $repo, EntityManagerInterface $manager, SupportChecker $supportChecker)
    {
        $this->repo = $repo;
        $this->manager = $manager;
        $this->supportChecker = $supportChecker;
        $this->disableListeners($this->manager);

        parent::__construct();
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $message = $this->update();
        $output->writeln("\e[30m\e[42m\n ".$message."\e[0m\n");

        return Command::SUCCESS;
    }

    protected function update()
    {
        $supports = $this->repo->findBy([], ['updatedAt' => 'DESC'], 1000);
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

        $this->manager->flush();

        return "[OK] The headers in support are checked !\n  ".$count.' / '.count($supports).' are invalids.';
    }
}
