<?php

namespace App\Command\Document;

use App\Repository\Support\DocumentRepository;
use App\Service\DoctrineTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Réorganise le classement des documents dans le dossier d'upload (TEMPORAIRE A SUPPRIMER).
 */
class ReorganizeUploadFolderCommand extends Command
{
    use DoctrineTrait;

    protected static $defaultName = 'app:document:reorganize_upload_folder';

    protected $documentRepo;
    protected $em;
    protected $output;

    public function __construct(DocumentRepository $documentRepo, EntityManagerInterface $em)
    {
        $this->documentRepo = $documentRepo;
        $this->em = $em;
        $this->disableListeners($this->em);

        parent::__construct();
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $documents = $this->documentRepo->findAll();
        $count = 0;

        foreach ($documents as $document) {
            $file = \dirname(__DIR__).'/../../public/uploads/documents/'.$document->getPeopleGroup()->getId().'/'.$document->getCreatedAt()->format('Y/m').'/'.$document->getInternalFileName();

            if (file_exists($file)) {
                $newPath = \dirname(__DIR__).'/../../public/uploads/documents/'.$document->getCreatedAt()->format('Y/m/d/').$document->getPeopleGroup()->getId().'/';
                $newFile = $newPath.$document->getInternalFileName();
                if (!file_exists($newPath)) {
                    mkdir($newPath, 0700, true);
                }
                if (copy($file, $newFile)) {
                    unlink($file);
                    ++$count;
                    $io->info($document->getId().' => OK');
                }
            }
        }
        $this->em->flush();

        $io->success("The document paths are update ! \n ".$count.' / '.count($documents)."\n");

        return Command::SUCCESS;
    }
}
