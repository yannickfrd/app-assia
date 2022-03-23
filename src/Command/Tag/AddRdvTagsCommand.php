<?php

namespace App\Command\Tag;

use App\Entity\Event\Rdv;
use App\Entity\Organization\Tag;
use App\Repository\Event\RdvRepository;
use App\Repository\Organization\TagRepository;
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
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if ('dev' !== $_SERVER['APP_ENV'] || 'localhost' !== $_SERVER['DB_HOST']) {
            $io->error('Invalid environnement!!');

            return Command::FAILURE;
        }

        $this->em->getFilters()->disable('softdeleteable');
        $this->disableListeners($this->em);

        /** @var TagRepository */
        $tagRepo = $this->em->getRepository(Tag::class);
        /** @var RdvRepository */
        $rdvRepo = $this->em->getRepository(Rdv::class);

        $tags = $tagRepo->findAll();
        $nbTags = count($tags);
        $nbRdvs = $rdvRepo->count([]);

        $io->progressStart($nbRdvs);

        foreach ($rdvRepo->findAll() as $rdv) {
            $tag = $tags[mt_rand(0, $nbTags - 1)];

            if (!$rdv->getTags()->contains($tag)) {
                $rdv->addTag($tag);
            }

            $io->progressAdvance();
        }

        $this->em->flush();

        $io->success('The rdv tags are added !!');

        return Command::SUCCESS;
    }
}
