<?php

namespace App\DataFixtures;

use App\Entity\Support\Note;
use App\Repository\Support\SupportGroupRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;

/*
 * @codeCoverageIgnore
 */
class E_NoteFixtures extends Fixture
{
    public function __construct(EntityManagerInterface $manager, SupportGroupRepository $repo)
    {
        $this->manager = $manager;
        $this->repo = $repo;
        $this->faker = \Faker\Factory::create('fr_FR');
    }

    public function load(ObjectManager $manager)
    {
        $supports = $this->repo->findAll();

        foreach ($supports as $support) {
            for ($i = 0; $i < mt_rand(1, 3); ++$i) {
                $content = '<p>'.join($this->faker->paragraphs(mt_rand(10, 15)), '</p><p>').'</p>';

                $note = (new Note())
                    ->setTitle($this->faker->sentence($nbWords = mt_rand(5, 10), $variableNbWords = true))
                    ->setContent($content)
                    ->setSupportGroup($support)
                    ->setCreatedAt(new \DateTime())
                    ->setCreatedBy($support->getReferent())
                    ->setUpdatedAt(new \DateTime())
                    ->setUpdatedBy($support->getReferent());

                $this->manager->persist($note);
            }
        }
        $this->manager->flush();
    }
}
