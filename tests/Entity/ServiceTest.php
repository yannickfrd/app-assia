<?php

namespace App\Tests\Entity;

use App\Entity\Service;
use App\Entity\Pole;
use App\Repository\ServiceRepository;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ServiceTest extends WebTestCase
{
    use FixturesTrait;
    use AsserthasErrorsTrait;

    /** @var Service */
    protected $service;

    /** @var Pole */
    protected $pole;


    protected function setUp()
    {
        $kernel = self::bootKernel();

        $entityManager = $kernel->getContainer()
            ->get("doctrine")
            ->getManager();

        $this->loadFixtureFiles([
            dirname(__DIR__, 2) . "/fixtures/UserFixtures.yaml",
            dirname(__DIR__, 2) . "/fixtures/ServiceFixtures.yaml",
            dirname(__DIR__, 2) . "/fixtures/PoleFixtures.yaml"
        ]);

        /** @var PoleRepository */
        $repoPole = $entityManager->getRepository(Pole::class);

        $this->pole = $repoPole->findOneBy(["name" => "Habitat"]);

        $this->service = $this->getService();
    }

    protected function getService()
    {
        $faker = \Faker\Factory::create("fr_FR");
        $now = new \DateTime();

        return (new Service())
            ->setName("Service " . $faker->numberBetween(1, 100))
            ->setCity($faker->city)
            ->setZipCode($faker->numberBetween(1, 95))
            ->setAddress($faker->address)
            ->setPole($this->pole)
            ->setCreatedAt($now)
            ->setUpdatedAt($now);
    }

    public function testValidService()
    {
        $this->assertHasErrors($this->service, 0);
    }

    public function testBlankName()
    {
        $this->assertHasErrors($this->service->setName(""), 1);
    }

    public function testNullPole()
    {
        $this->assertHasErrors($this->service->setPole(null), 1);
    }

    public function testInvalidEmail()
    {
        $this->assertHasErrors($this->service->setEmail("xxx@xxx"), 1);
    }

    public function testServiceExists()
    {
        $service = $this->service
            ->setName("AVDL");
        $this->assertHasErrors($service, 1);
    }
}
