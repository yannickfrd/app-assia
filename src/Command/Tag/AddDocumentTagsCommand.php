<?php

namespace App\Command\Tag;

use App\Entity\Organization\Tag;
use App\Entity\Support\Document;
use App\Repository\Support\DocumentRepository;
use App\Service\DoctrineTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class AddDocumentTagsCommand extends Command // TEMPORAIRE A SUPPRIMER
{
    use DoctrineTrait;

    protected static $defaultName = 'app:document:add_tags';

    /** @var EntityManagerInterface */
    private $em;

    /** @var SymfonyStyle */
    private $io;

    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct();
        $this->em = $em;
        $this->em->getFilters()->disable('softdeleteable');
        $this->disableListeners($this->em);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);

        $tagRepo = $this->em->getRepository(Tag::class);
        /** @var DocumentRepository */
        $documentRepo = $this->em->getRepository(Document::class);

        $nbDocumentWithType = $documentRepo->count([]) - $documentRepo->count(['type' => null]);

        $this->io->progressStart($nbDocumentWithType);

        foreach ($tagRepo->findAll() as $tag) {
            /** @var Document[] $documents */
            $documents = $documentRepo->findBy(['type' => $tag->getCode()]);
            /** @var Tag $tag */
            $tag = $tagRepo->findOneBy(['name' => $tag->getName()]);

            foreach ($documents as $document) {
                $document->addTag($tag);
                $this->io->progressAdvance();
            }
        }

        $this->em->flush();

        $this->io->success('The document tags are added !!');

        return Command::SUCCESS;
    }

    public function documentUpdate(): void
    {
    }
}
