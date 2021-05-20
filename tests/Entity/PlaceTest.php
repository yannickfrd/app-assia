<?php

namespace App\Tests\Entity;

use App\Entity\Organization\Place;
use App\Entity\Organization\Service;
use App\Tests\Entity\AssertHasErrorsTrait;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PlaceTest extends WebTestCase
{
    use FixturesTrait;
    use AssertHasErrorsTrait;

    /** @var \Doctrine\ORM\EntityManager */
    private $entityManager;

    /** @var Place */
    protected $place;

    /** @var Service */
    protected $service;

    protected function setUp()
    {
        $data = $this->loadFixtureFiles([
            dirname(__DIR__).'/DataFixturesTest/UserFixturesTest.yaml',
            dirname(__DIR__).'/DataFixturesTest/ServiceFixturesTest.yaml',
            dirname(__DIR__).'/DataFixturesTest/PlaceFixturesTest.yaml',
        ]);

        $this->place = $this->getPlace();

        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->service = $data['service1'];
    }

    protected function getPlace()
    {
        $faker = \Faker\Factory::create('fr_FR');
        $now = new \DateTime();

        return (new Place())
            ->setName('Logement '.$faker->numberBetween(1, 100))
            ->setNbPlaces($faker->numberBetween(1, 10))
            ->setStartDate($faker->dateTimeBetween('-10 years', 'now'))
            ->setCity($faker->city)
            ->setZipcode($faker->numberBetween(1, 95))
            ->setAddress($faker->address);
    }

    public function testValidPlace()
    {
        $this->assertHasErrors($this->place, 0);
    }

    public function testBlankName()
    {
        $this->assertHasErrors($this->place->setName(''), 1);
    }

    public function testNbPlaces()
    {
        $this->assertHasErrors($this->place->setNbPlaces(0), 0);
    }

    public function testNullStartDate()
    {
        $this->assertHasErrors($this->place->setStartDate(null), 0);
    }

    public function testNullEndDate()
    {
        $this->assertHasErrors($this->place->setEndDate(null), 0);
    }

    public function testPlaceExists()
    {
        $place = $this->place
            ->setName('Logement 666')
            ->setService($this->service);

        $this->assertHasErrors($place, 1);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
        $this->entityManager = null;
        $this->service = null;
    }
}
