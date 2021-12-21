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

    public const TYPE = [
        2 => 'Administratif',
        10 => 'Dettes',
        9 => 'Emploi',
        1 => 'Identité/Etat civil',
        4 => 'Impôts',
        6 => 'Logement',
        8 => 'Orientation',
        5 => 'Redevance',
        3 => 'Ressources',
        7 => 'Santé',
        11 => 'Demande de logement',
        12 => 'Demande SIAO',
        13 => 'Enfance',
        14 => 'Hôtel',
        97 => 'Autre',
    ];

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

    protected function configure(): void
    {
        $this
            ->setDescription('Create a default tags on Tag entity.')
            ->setDescription('This command generate tags by default in the Tag entity. No arguments.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);

        $this->tagsGenerate();

        $this->documentUpdate();

        $this->io->success('This command was successfully completed !!');

        return Command::SUCCESS;
    }

    public function tagsGenerate(): void
    {
        $tagRepo = $this->em->getRepository(Tag::class);

        foreach (self::TYPE as $value) {
            if (null === $tagRepo->findOneBy(['name' => $value])) {
                $tag = (new Tag())->setName($value);
                $this->em->persist($tag);
            }
        }
        $this->em->flush();
    }

    public function documentUpdate(): void
    {
        $tagRepo = $this->em->getRepository(Tag::class);
        /** @var DocumentRepository */
        $documentRepo = $this->em->getRepository(Document::class);

        $nbDocumentWithType = $documentRepo->count([]) - $documentRepo->count(['type' => null]);

        $this->io->progressStart($nbDocumentWithType);

        foreach (self::TYPE as $key => $value) {
            /** @var Document $documents */
            $documents = $documentRepo->findBy(['type' => $key]);
            /** @var Tag $tag */
            $tag = $tagRepo->findOneBy(['name' => $value]);

            foreach ($documents as $document) {
                $document->addTag($tag);
                $this->io->progressAdvance();
            }
        }
        $this->em->flush();
    }
}
