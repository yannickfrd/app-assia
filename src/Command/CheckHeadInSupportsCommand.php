<?php

namespace App\Command;

use App\EntityManager\SupportManager;
use App\Repository\Support\SupportGroupRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Commande pour vérifier le demandeur principal dans chaque groupe et suivi.
 */
class CheckHeadInSupportsCommand extends Command
{
    use DoctrineTrait;

    protected static $defaultName = 'app:support:check_head';

    protected $repo;
    protected $manager;
    protected $supportManager;

    public function __construct(SupportGroupRepository $repo, EntityManagerInterface $manager, SupportManager $supportManager)
    {
        $this->repo = $repo;
        $this->manager = $manager;
        $this->supportManager = $supportManager;
        $this->disableListeners();

        parent::__construct();
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $message = $this->checkHeadInSupports();
        $output->writeln("\e[30m\e[42m\n ".$message."\e[0m\n");

        return 0;
    }

    /**
     * Mettre à jour le nb de personnes.
     */
    protected function checkHeadInSupports()
    {
        $count = 0;
        $supports = $this->repo->findAll();
        foreach ($supports as $support) {
            $countHeads = 0;
            foreach ($support->getSupportPeople() as $supportPerson) {
                if ($supportPerson->getHead()) {
                    ++$countHeads;
                }
            }
            if ($countHeads != 1) {
                echo $support->getId()." => $countHeads DP\n";
                $this->supportManager->checkValidHead($support);
                ++$count;
            }
        }

        $this->manager->flush();

        return "[OK] The headers in support are checked !\n  ".$count.' / '.count($supports).' are invalids.';
    }
}
