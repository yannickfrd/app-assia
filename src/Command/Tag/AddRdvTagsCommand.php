<?php

namespace App\Command\Tag;

use App\Entity\Organization\Tag;
use App\Entity\Support\Rdv;
use App\Repository\Organization\TagRepository;
use App\Repository\Support\RdvRepository;
use App\Service\DoctrineTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class AddRdvTagsCommand extends Command // TEMPORAIRE A SUPPRIMER
{
    use DoctrineTrait;

    protected static $defaultName = 'app:rdv:add_tags';

    /** @var EntityManagerInterface */
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct();
        $this->em = $em;
        $this->em->getFilters()->disable('softdeleteable');
        $this->disableListeners($this->em);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if ('dev' !== $_SERVER['APP_ENV'] || 'localhost' !== $_SERVER['DB_HOST']) {
            $io->error('Environnement invalid!');

            return Command::FAILURE;
        }

        /** @var TagRepository */
        $tagRepo = $this->em->getRepository(Tag::class);
        /** @var RdvRepository */
        $rdvRepo = $this->em->getRepository(Rdv::class);

        $tags = $tagRepo->findAll();
        $nbTags = count($tags);
        $nbRdvs = $rdvRepo->count([]);

        $io->progressStart($nbRdvs);

        foreach ($rdvRepo->findAll() as $rdv) {
            $rdv->addTag($tags[mt_rand(0, $nbTags - 1)]);
            $io->progressAdvance();
        }

        $this->em->flush();

        $io->success('This command was successfully completed !!');

        return Command::SUCCESS;
    }
}
