<?php

namespace App\DataFixtures;

use App\Entity\Support\Document;
use App\Repository\Support\SupportGroupRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\String\Slugger\SluggerInterface;

/*
 * @codeCoverageIgnore
 */
class E_DocumentFixtures extends Fixture
{
    private $manager;
    private $supportGroupRepo;
    private $slugger;
    private $faker;

    public function __construct(EntityManagerInterface $manager, SluggerInterface $slugger, SupportGroupRepository $supportGroupRepo)
    {
        $this->manager = $manager;
        $this->supportGroupRepo = $supportGroupRepo;
        $this->slugger = $slugger;
        $this->faker = \Faker\Factory::create('fr_FR');
    }

    public function load(ObjectManager $manager)
    {
        foreach ($this->supportGroupRepo->findAll() as $support) {
            for ($i = 0; $i < mt_rand(6, 10); ++$i) {
                $createdAt = AppFixtures::getDateTimeBeetwen('-12 months', 'now');

                $document = (new Document())
                ->setName($name = $this->faker->sentence(mt_rand(2, 5), true))
                ->setInternalFileName('/documents/'.$createdAt->format('Y/m/d/').strtolower($this->slugger->slug($name)))
                ->setPeopleGroup($support->getPeopleGroup())
                ->setType(mt_rand(1, 10))
                ->setSupportGroup($support)
                ->setCreatedAt($createdAt)
                ->setCreatedBy($support->getReferent())
                ->setUpdatedAt($createdAt)
                ->setUpdatedBy($support->getReferent());

                $this->manager->persist($document);
            }
        }
        $this->manager->flush();
    }
}
