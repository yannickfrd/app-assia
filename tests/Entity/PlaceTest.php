<?php

namespace App\Tests\Entity;

use App\Entity\Organization\Place;
use App\Entity\Organization\Service;
use App\Tests\Entity\AssertHasErrorsTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;

class PlaceTest extends WebTestCase
{
    use AssertHasErrorsTrait;

    /** @var \Doctrine\ORM\EntityManager */
    private $entityManager;

    /** @var Place */
    protected $place;

    /** @var Service */
    protected $service;

    protected function setUp(): void
    {
        $this->place = $this->getPlace();

        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();
    }

    protected function getPlace(): Place
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

    public function testValidPlace(): void
    {
        $this->assertHasErrors($this->place, 0);
    }

    public function testBlankName(): void
    {
        $this->assertHasErrors($this->place->setName(''), 1);
    }

    public function testNbPlaces(): void
    {
        $this->assertHasErrors($this->place->setNbPlaces(0), 0);
    }

    public function testNullStartDate(): void
    {
        $this->assertHasErrors($this->place->setStartDate(null), 0);
    }

    public function testNullEndDate(): void
    {
        $this->assertHasErrors($this->place->setEndDate(null), 0);
    }

    public function testPlaceExists(): void
    {
        /** @var AbstractDatabaseTool */
        $databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();

        $fixtures = $databaseTool->loadAliceFixture([
            dirname(__DIR__).'/fixtures/app_fixtures_test.yaml',
            dirname(__DIR__).'/fixtures/service_fixtures_test.yaml',
            dirname(__DIR__).'/fixtures/place_fixtures_test.yaml',
        ]);

        $place = $this->place
            ->setName('Logement test')
            ->setService($fixtures['service1']);

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
