<?php

namespace App\Tests\Entity;

use App\Entity\Accommodation;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AccommodationTest extends WebTestCase
{
    use FixturesTrait;

    /** @var Accommodation */
    protected $accommodation;

    protected function setUp()
    {
        $this->accommodation = $this->getAccommodation();
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
}
