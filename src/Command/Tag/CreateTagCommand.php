<?php

namespace App\Command\Tag;

use App\Entity\Organization\Service;
use App\Entity\Organization\Tag;
use App\Form\Utils\Choices;
use App\Repository\Organization\ServiceRepository;
use App\Service\DoctrineTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:tag:create_one',
    description: 'Create a new tag.',
)]
class CreateTagCommand extends Command
{
    use DoctrineTrait;

    /** @var EntityManagerInterface */
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct();

        $this->em = $em;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->em->getFilters()->disable('softdeleteable');
        $this->disableListeners($this->em);

        $io = new SymfonyStyle($input, $output);

        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');

        $this->em->getFilters()->disable('softdeleteable');
        $this->disableListeners($this->em);

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
