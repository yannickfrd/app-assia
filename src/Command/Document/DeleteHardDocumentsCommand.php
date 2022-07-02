<?php

namespace App\Command\Document;

use App\Repository\Support\DocumentRepository;
use App\Service\DoctrineTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:document:delete_hard',
    description: 'Delete really the soft-deleted documents and associate files.',
)]
class DeleteHardDocumentsCommand extends Command
{
    use DoctrineTrait;

    protected $documentRepo;
    protected $em;
    protected $documentsDirectory;
    protected $output;

    public function __construct(DocumentRepository $documentRepo, EntityManagerInterface $em, string $documentsDirectory)
    {
        parent::__construct();

        $this->documentRepo = $documentRepo;
        $this->em = $em;
        $this->documentsDirectory = $documentsDirectory;
    }

    protected function configure(): void
    {
        $this
        ->addArgument('nb_months', InputArgument::OPTIONAL, 'Number of months before today.')
        ->setHelp('This command delete really the soft-deleted documents and associate files.');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $nbMonths = $input->getArgument('nb_months') ?? 12;
        $date = (new \DateTime())->modify("-{$nbMonths} months");

        $this->disableListeners($this->em);
        $this->em->getFilters()->disable('softdeleteable');

        $documents = $this->documentRepo->findSoftDeletedDocuments($date);
        $count = 0;

        foreach ($documents as $document) {
            $nbFiles = $this->documentRepo->count([
                'peopleGroup' => $document->getSupportGroup()->getPeopleGroup(),
                'internalFileName' => $document->getInternalFileName(),
            ]);

            $path = $this->documentsDirectory.$document->getPath();
            $file = $path.$document->getInternalFileName();

            if (1 === $nbFiles && \file_exists($file)) {
                \unlink($file);
                $io->info($document->getId().' => deleted');
                ++$count;
            }

            $this->em->remove($document);
        }
        $this->em->flush();

        $io->success("The documents are deleted ! \n ".$count.' / '.count($documents)."\n");

        return Command::SUCCESS;
    }
}
