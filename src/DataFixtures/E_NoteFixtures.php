<?php

namespace App\DataFixtures;

use App\Entity\Support\Note;
use App\Repository\Support\SupportGroupRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;

/*
 * @codeCoverageIgnore
 */
class E_NoteFixtures extends Fixture
{
    private $em;
    private $supportGroupRepo;
    private $faker;

    public function __construct(EntityManagerInterface $em, SupportGroupRepository $supportGroupRepo)
    {
        $this->em = $em;
        $this->supportGroupRepo = $supportGroupRepo;
        $this->faker = \Faker\Factory::create('fr_FR');
    }

    public function load(ObjectManager $em): void
    {
        foreach ($this->supportGroupRepo->findAll() as $support) {
            for ($i = 0; $i < mt_rand(1, 3); ++$i) {
                $content = '<p>'.join('</p><p>', $this->faker->paragraphs(mt_rand(10, 15))).'</p>';

                $note = (new Note())
                    ->setTitle($this->faker->sentence($nbWords = mt_rand(5, 10), $variableNbWords = true))
                    ->setContent($content)
                    ->setSupportGroup($support)
                    ->setCreatedAt(new \DateTime())
                    ->setCreatedBy($support->getReferent())
                    ->setUpdatedAt(new \DateTime())
                    ->setUpdatedBy($support->getReferent());

                $this->em->persist($note);
            }
        }
        $this->em->flush();
    }
}
