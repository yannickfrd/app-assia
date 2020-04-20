<?php

namespace App\Tests\Entity;

use App\Entity\Accommodation;
use App\Entity\Service;
use Liip\TestFixturesBundle\Test\FixturesTrait;
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
        $dataFixtures = $this->loadFixtureFiles([
            dirname(__DIR__).'/DataFixturesTest/AccommodationFixturesTest.yaml',
        ]);

        $this->accommodation = $this->getAccommodation();

        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->service = $dataFixtures['service'];
    }

    protected function getAccommodation()
    {
        $faker = \Faker\Factory::create('fr_FR');
        $now = new \DateTime();

        return (new Accommodation())
            ->setName('Logement '.$faker->numberBetween(1, 100))
            ->setPlacesNumber($faker->numberBetween(1, 10))
            ->setOpeningDate($faker->dateTimeBetween('-10 years', 'now'))
            ->setCity($faker->city)
            ->setZipcode($faker->numberBetween(1, 95))
            ->setAddress($faker->address);
    }

    public function testValidAccommodation()
    {
        $this->assertHasErrors($this->accommodation, 0);
    }

    public function testBlankName()
    {
        $this->assertHasErrors($this->accommodation->setName(''), 1);
    }

    public function testNotNullPlaceNumber()
    {
        $this->assertHasErrors($this->accommodation->setPlacesNumber(null), 1);
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
            ->setName('Logement 666')
            ->setService($this->service);

        $this->assertHasErrors($accommodation, 1);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
        $this->entityManager = null;
        $this->service = null;
    }
}
