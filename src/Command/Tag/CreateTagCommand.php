<?php

namespace App\Command\Tag;

use App\Entity\Organization\Service;
use App\Entity\Organization\Tag;
use App\Form\Utils\Choices;
use App\Repository\Organization\ServiceRepository;
use App\Service\DoctrineTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

class CreateTagCommand extends Command
{
    use DoctrineTrait;

    protected static $defaultName = 'app:tag:create_one';

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

        $helper = $this->getHelper('question');

        /** @var ServiceRepository $serviceRepo */
        $serviceRepo = $this->em->getRepository(Service::class);

        $nameQuestion = new Question("<info>Tag name</info>:\n> ");
        $name = $helper->ask($input, $output, $nameQuestion);

        $codeQuestion = new Question("<info>Tag code</info>:\n> ");
        $code = $helper->ask($input, $output, $codeQuestion);

        $colorQuestion = (new ChoiceQuestion(
            '<info>Tag color</info> :',
            Tag::COLORS,
            Tag::DEFAULT_COLOR
        ));
        $color = $helper->ask($input, $output, $colorQuestion);

        $addToServicesQuestion = (new ChoiceQuestion(
            '<info>Add to all services</info> [default: No]:',
            ['Yes', 'No'],
            Choices::NO
        ));

        $addToServices = $helper->ask($input, $output, $addToServicesQuestion);

        $tag = (new Tag())
            ->setName($name)
            ->setCode($code)
            ->setColor($color);

        if ('Yes' === $addToServices) {
            foreach ($serviceRepo->findAll() as $service) {
                $service->addTag($tag);
            }
        }

        $this->em->persist($tag);
        $this->em->flush();

        $io->success('The tag is created !!');

        return Command::SUCCESS;
    }
}
