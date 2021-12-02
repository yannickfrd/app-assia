<?php

namespace App\Tests\Entity;

use App\Entity\Organization\Pole;
use App\Entity\Organization\Service;
use App\Tests\Entity\AssertHasErrorsTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ServiceTest extends WebTestCase
{
    use AssertHasErrorsTrait;

    /** @var \Doctrine\ORM\EntityManager */
    private $entityManager;

    /** @var Service */
    protected $service;

    /** @var Pole */
    protected $pole;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $faker = \Faker\Factory::create('fr_FR');

        $this->service = (new Service())
            ->setName('Service '.$faker->numberBetween(1, 100))
            ->setCity($faker->city)
            ->setZipcode($faker->numberBetween(1, 95))
            ->setAddress($faker->address)
            ->setPole(new Pole());
    }

    public function testValidService()
    {
        $this->assertHasErrors($this->service, 0);
    }

    public function testBlankName()
    {
        $this->assertHasErrors($this->service->setName(''), 1);
    }

    public function testNullPole()
    {
        $this->assertHasErrors($this->service->setPole(null), 1);
    }

    public function testInvalidEmail()
    {
        $this->assertHasErrors($this->service->setEmail('xxx@xxx'), 1);
    }

    public function testServiceExists()
    {
        $service = $this->service
            ->setName('CHRS Cergy');
        $this->assertHasErrors($service, 1);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
        $this->entityManager = null;
        $this->pole;
        $this->service;
    }
}
