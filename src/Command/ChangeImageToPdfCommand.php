<?php

namespace App\Command;

use App\Repository\Support\DocumentRepository;
use App\Service\DoctrineTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
        $this->output = $output;
        $message = $this->update();
        $output->writeln("\e[30m\e[42m\n ".$message."\e[0m\n");

        return 0;
    }

    protected function update()
    {
        $documents = $this->repo->findAll();
        $count = 0;

        foreach ($documents as $document) {

        }
        $this->manager->flush();

        return "\n[OK] The document paths are update ! \n ".$count.' / '.count($documents)."\n";
    }
}
