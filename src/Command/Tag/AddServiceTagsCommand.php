<?php

namespace App\Command\Tag;

use App\Entity\Organization\Service;
use App\Entity\Organization\Tag;
use App\Repository\Organization\ServiceRepository;
use App\Repository\Organization\TagRepository;
use App\Service\DoctrineTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class AddServiceTagsCommand extends Command // TEMPORAIRE A SUPPRIMER
{
    use DoctrineTrait;

    protected static $defaultName = 'app:service:add_tags';

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

        /** @var TagRepository */
        $tagRepo = $this->em->getRepository(Tag::class);
        /** @var ServiceRepository $serviceRepo */
        $serviceRepo = $this->em->getRepository(Service::class);

        $tags = $tagRepo->findAll();
        $nbServices = $serviceRepo->count([]);

        $io->progressStart($nbServices);

        foreach ($serviceRepo->findAll() as $service) {
            foreach ($tags as $tag) {
                if (!$service->getTags()->contains($tag)) {
                    $service->addTag($tag);
                }
            }
            $io->progressAdvance();
        }

        $this->em->flush();

        $io->success('The service tags are added !!');

        return Command::SUCCESS;
    }
}
