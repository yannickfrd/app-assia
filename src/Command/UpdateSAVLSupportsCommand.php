<?php

namespace App\Command;

use App\Entity\Support\Avdl;
use App\Repository\Support\SupportGroupRepository;
use App\Service\DoctrineTrait;
use App\Service\SupportGroup\SupportChecker;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Met à jour les suivis SAVL.
 */
class UpdateSAVLSupportsCommand extends Command
{
    use DoctrineTrait;

    protected static $defaultName = 'app:support:update_savl';

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
        $supports = $this->repo->findBy([
            'service' => 4,
        ], ['updatedAt' => 'DESC'], 1000);
        $count = 0;

        foreach ($supports as $support) {
            if (!$support->getAvdl() && $support->getStartDate()) {
                $avdl = (new Avdl())
                    ->setSupportStartDate($support->getStartDate())
                    ->setSupportGroup($support);
                $this->manager->persist($avdl);
                ++$count;
            }
        }

        $this->manager->flush();

        return "[OK] TheAVDL supports are updates !\n  ".$count.' / '.count($supports);
    }
}
