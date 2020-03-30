<?php

namespace App\Tests\Entity;

use App\Entity\SupportGroup;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SupportGroupTest extends WebTestCase
{
    use FixturesTrait;
    use AsserthasErrorsTrait;

    /** @var SupportGroup */
    protected $supportGroup;

    protected function setUp()
    {
        $kernel = self::bootKernel();

        $dataFixutres = $this->loadFixtureFiles([
            dirname(__DIR__) . "/DataFixtures/SupportGroupFixturesTest.yaml",
        ]);

        $this->supportGroup = (new SupportGroup())
            ->setStartDate(new \DateTime("2020-01-01"))
            ->setStatus(2)
            ->setAgreement(true)
            ->setReferent($dataFixutres["user"])
            ->setService($dataFixutres["service"])
            ->setDevice($dataFixutres["device"]);
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
        $this->supportGroup = null;
    }
}
