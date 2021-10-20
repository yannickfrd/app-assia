<?php

namespace App\Command\Document;

use App\Repository\Support\DocumentRepository;
use App\Service\DoctrineTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Delete really the soft-deleted documents and associate files.
 */
class DeleteHardDocumentsCommand extends Command
{
    use DoctrineTrait;

    protected static $defaultName = 'app:document:delete_hard';

    protected $documentRepo;
    protected $manager;
    protected $documentsDirectory;
    protected $output;

    public function __construct(DocumentRepository $documentRepo, EntityManagerInterface $manager, string $documentsDirectory)
    {
        $this->documentRepo = $documentRepo;
        $this->manager = $manager;
        $this->documentsDirectory = $documentsDirectory;
        $this->disableListeners($this->manager);
        $this->manager->getFilters()->disable('softdeleteable');

        parent::__construct();
    }

    protected function configure()
    {
        $this
        ->addArgument('nb_months', InputArgument::OPTIONAL, 'Number of months before today.')
        ->setHelp('This command delete really the soft-deleted documents and associate files.');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $nbMonths = $input->getArgument('nb_months') ?? 12;
        $date = (new \Datetime())->modify("-{$nbMonths} months");

        $documents = $this->documentRepo->findSoftDeletedDocuments($date);
        $count = 0;

        foreach ($documents as $document) {
            $nbFiles = $this->documentRepo->count([
                'peopleGroup' => $document->getSupportGroup()->getPeopleGroup(),
                'internalFileName' => $document->getInternalFileName(),
            ]);

            $path = $this->documentsDirectory.$document->getCreatedAt()->format('Y/m/d/').$document->getPeopleGroup()->getId().'/';
            $file = $path.$document->getInternalFileName();

            if (1 === $nbFiles && \file_exists($file)) {
                \unlink($file);
                $io->info($document->getId().' => deleted');
                ++$count;
            }

            $this->manager->remove($document);
        }
        $this->manager->flush();

        $io->success("The documents are deleted ! \n ".$count.' / '.count($documents)."\n");

        return Command::SUCCESS;
    }
}
