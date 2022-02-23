<?php

namespace App\Command\Evaluation;

use App\Entity\Evaluation\EvalBudgetResource;
use App\Entity\Evaluation\Resource;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/*
 * Créé les ressources, les charges et les dettes de toutes les personnes. TEMPORAIRE A SUPPRIMER.
 */
class EvaluationCreateResourcesCommand extends Command
{
    protected static $defaultName = 'app:evaluation:create_resources';

    protected $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;

        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $evalBudgetResources = $this->em->getRepository(EvalBudgetResource::class)->findAll();

        $io->createProgressBar();
        $io->progressStart(count($evalBudgetResources));

        $resourceRepo = $this->em->getRepository(Resource::class);

        $resources = [];
        foreach (Resource::RESOURCES as $key => $value) {
            if (!$resource = $resourceRepo->findOneBy(['name' => $value])) {
                $resource = (new Resource())
                    ->setCode($key)
                    ->setName($value);

                $this->em->persist($resource);
            }

            $resources[$resource->getCode()] = $resource;
        }

        /**
         * @var EvalBudgetResource[] $evalBudgetResources
         */
        foreach ($evalBudgetResources as $evalBudgetResource) {
            $evalBudgetResource->setResource($resources[$evalBudgetResource->getType()]);
            $io->progressAdvance();
        }

        $this->em->flush();

        $io->progressFinish();

        $io->success("It's successful !");

        return Command::SUCCESS;
    }
}
