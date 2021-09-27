<?php

namespace App\Command;

use App\Repository\Support\DocumentRepository;
use App\Service\DoctrineTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Delete really the soft-deleted documents and associate files.
 */
class DeleteHardDocumentsCommand extends Command
{
    use DoctrineTrait;

    protected static $defaultName = 'app:document:delete_hard';

    protected $repo;
    protected $manager;
    protected $documentsDirectory;
    protected $output;

    public function __construct(DocumentRepository $repo, EntityManagerInterface $manager, string $documentsDirectory)
    {
        $this->repo = $repo;
        $this->manager = $manager;
        $this->documentsDirectory = $documentsDirectory;
        $this->disableListeners($this->manager);
        $this->manager->getFilters()->disable('softdeleteable');

        parent::__construct();
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $nbMonths = $input->getArgument('nb_months') ?? 12;
        $date = (new \Datetime())->modify("-{$nbMonths} months");

        $this->output = $output;
        $message = $this->delete($date);
        $output->writeln("\e[30m\e[42m\n ".$message."\e[0m\n");

        return Command::SUCCESS;
    }

    protected function delete(\Datetime $date)
    {
        $documents = $this->repo->findSoftDeletedDocuments($date);
        $count = 0;

        foreach ($documents as $document) {
            $nbFiles = $this->repo->count([
                'peopleGroup' => $document->getSupportGroup()->getPeopleGroup(),
                'internalFileName' => $document->getInternalFileName(),
            ]);

            $path = $this->documentsDirectory.$document->getCreatedAt()->format('Y/m/d/').$document->getPeopleGroup()->getId().'/';
            $file = $path.$document->getInternalFileName();

            if (1 === $nbFiles && \file_exists($file)) {
                \unlink($file);
                $this->output->writeln($document->getId().' => deleted');
                ++$count;
            }

            $this->manager->remove($document);
        }
        $this->manager->flush();

        return "\n[OK] The documents are deleted ! \n ".$count.' / '.count($documents)."\n";
    }

    protected function configure()
    {
        $this
        ->addArgument('nb_months', InputArgument::OPTIONAL, 'Number of months before today.')
        ->setHelp('This command delete really the soft-deleted documents and associate files.');
    }
}
