<?php

namespace App\Command\Tag;

use App\Entity\Organization\Tag;
use App\Service\DoctrineTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CreateAllTagsCommand extends Command // TEMPORAIRE A SUPPRIMER
{
    use DoctrineTrait;

    protected static $defaultName = 'app:tag:create_all';

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
        11 => 'DLS',
        // 12 => 'Demande SIAO',
        // 13 => 'Enfance',
        // 14 => 'Hôtel',
        97 => 'Divers',
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
            ->setDescription('This command generate default tags in the Tag entity. No arguments.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);

        $tagRepo = $this->em->getRepository(Tag::class);

        foreach (self::TYPE as $key => $value) {
            if (null === $tagRepo->findOneBy(['name' => $value])) {
                $tag = (new Tag())
                    ->setCode($key)
                    ->setName($value);

                $this->em->persist($tag);
            }
        }

        $this->em->flush();

        $this->io->success('The tags are created !!');

        return Command::SUCCESS;
    }
}
