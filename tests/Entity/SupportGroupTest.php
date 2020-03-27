<?php

namespace App\Tests\Entity;

use App\Entity\Pole;
use App\Entity\User;
use App\Entity\Device;
use App\Entity\Service;
use App\Entity\SupportGroup;
use App\Repository\SupportGroupRepository;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SupportGroupTest extends WebTestCase
{
    use FixturesTrait;
    use AsserthasErrorsTrait;

    /** @var \Doctrine\ORM\EntityManager */
    private $entityManager;

    /** @var SupportGroup */
    protected $supportGroup;

    /** @var User */
    protected $user;

    /** @var Service */
    protected $service;

    /** @var Device */
    protected $device;


    protected function setUp()
    {
        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get("doctrine")
            ->getManager();

        $this->loadFixtureFiles([
            dirname(__DIR__) . "/Datafixtures/SupportGroupFixturesTest.yaml",
        ]);

        /** @var UserRepository */
        $repoUser = $this->entityManager->getRepository(User::class);

        /** @var ServiceRepository */
        $repoService = $this->entityManager->getRepository(Service::class);

        /** @var DeviceRepository */
        $repoDevice = $this->entityManager->getRepository(Device::class);

        $this->user = $repoUser->findOneBy(["username" => "r.madelaine"]);
        $this->service = $repoService->findOneBy(["name" => "AVDL"]);
        $this->device = $repoDevice->findOneBy(["name" => "AVDL"]);

        $this->supportGroup = $this->getSupportGroup();
    }

    protected function getSupportGroup()
    {
        $faker = \Faker\Factory::create("fr_FR");
        $now = new \DateTime();

        return (new SupportGroup())
            ->setStartDate(new \DateTime("2020-01-01"))
            ->setStatus(2)
            ->setAgreement(true)
            ->setReferent($this->user)
            ->setService($this->service)
            ->setDevice($this->device);
    }

    public function testValidSupportGroup()
    {
        $this->assertHasErrors($this->supportGroup, 0);
    }

    public function testNullStatusSupportGroup()
    {
        $this->assertHasErrors($this->supportGroup->setStatus(null), 1);
    }

    protected function tearDown()
    {
        parent::tearDown();
        $this->entityManager->close();
        $this->entityManager = null;
    }
}
