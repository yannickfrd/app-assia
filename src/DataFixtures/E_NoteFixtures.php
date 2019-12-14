<?php

namespace App\DataFixtures;

use App\Entity\Note;
use App\Repository\SupportGroupRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class E_NoteFixtures extends Fixture
{

    public function __construct(ObjectManager $manager, SupportGroupRepository $repo)
    {
        $this->manager = $manager;
        $this->repo = $repo;
        $this->faker = \Faker\Factory::create("fr_FR");
    }

    public function load(ObjectManager $manager)
    {
        $supports = $this->repo->findAll();

        foreach ($supports as $support) {

            for ($i = 0; $i < mt_rand(5, 10); $i++) {

                $note = new Note();

                $content = "<p>" . join($this->faker->paragraphs(mt_rand(10, 15)), "</p><p>") . "</p>";

                $note->setTitle($this->faker->sentence($nbWords = mt_rand(5, 10), $variableNbWords = true))
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
