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

    protected $repo;
    protected $manager;
    protected $output;

    public function __construct(DocumentRepository $repo, EntityManagerInterface $manager)
    {
        $this->repo = $repo;
        $this->manager = $manager;
        $this->disableListeners($this->manager);

        parent::__construct();
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $documents = $this->repo->findAll();
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
        $this->manager->flush();

        $io->success("The document paths are update ! \n ".$count.' / '.count($documents)."\n");

        return Command::SUCCESS;
    }
}
