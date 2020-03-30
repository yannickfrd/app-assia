<?php

namespace App\Tests\Entity;

use App\Entity\Service;
use App\Entity\Accommodation;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use App\Tests\Entity\AssertHasErrorsTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AccommodationTest extends WebTestCase
{
    use FixturesTrait;
    use AssertHasErrorsTrait;

    /** @var \Doctrine\ORM\EntityManager */
    private $entityManager;

    /** @var Accommodation */
    protected $accommodation;

    /** @var Service */
    protected $service;


    protected function setUp()
    {
        $this->accommodation = $this->getAccommodation();

        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get("doctrine")
            ->getManager();

        $dataFixtures = $this->loadFixtureFiles([
            dirname(__DIR__) . "/DataFixturesTest/AccommodationFixturesTest.yaml",
        ]);

        $this->service = $dataFixtures["service"];
    }

    protected function getAccommodation()
    {
        $faker = \Faker\Factory::create("fr_FR");
        $now = new \DateTime();

        return (new Accommodation())
            ->setName("Logement " . $faker->numberBetween(1, 100))
            ->setPlacesNumber($faker->numberBetween(1, 10))
            ->setOpeningDate($faker->dateTimeBetween("-10 years", "now"))
            ->setCity($faker->city)
            ->setDepartment($faker->numberBetween(1, 95))
            ->setAddress($faker->address)
            ->setCreatedAt($now)
            ->setUpdatedAt($now);
    }

    public function testValidAccommodation()
    {
        $this->assertHasErrors($this->accommodation, 0);
    }

    public function testBlankName()
    {
        $this->assertHasErrors($this->accommodation->setName(""), 1);
    }

    public function testNullOrBlankPlaceNumber()
    {
        $this->assertHasErrors($this->accommodation->setPlacesNumber(null), 2);
    }

    public function testPlaceNumber()
    {
        $this->assertHasErrors($this->accommodation->setPlacesNumber(0), 0);
    }

    public function testNullOpeningDate()
    {
        $this->assertHasErrors($this->accommodation->setOpeningDate(null), 1);
    }

    public function testNullClosingDate()
    {
        $this->assertHasErrors($this->accommodation->setClosingDate(null), 0);
    }

    public function testAccommodationExists()
    {
        $accommodation = $this->accommodation
            ->setName("Logement 666")
            ->setService($this->service);

        $this->assertHasErrors($accommodation, 1);
    }

    protected function tearDown()
    {
        parent::tearDown();
        $this->entityManager->close();
        $this->entityManager = null;
        $this->service = null;
    }
}
