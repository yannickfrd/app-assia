<?php

namespace App\Tests\Entity;

use App\Entity\Organization\Device;
use App\Entity\Organization\Service;
use App\Entity\Organization\User;
use App\Entity\Support\SupportGroup;
use App\Tests\Entity\AssertHasErrorsTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SupportGroupTest extends WebTestCase
{
    use AssertHasErrorsTrait;

    /** @var SupportGroup */
    protected $supportGroup;

    protected function setUp(): void
    {
        $this->supportGroup = (new SupportGroup())
            ->setStartDate(new \DateTime('2020-01-01'))
            ->setStatus(2)
            ->setAgreement(true)
            ->setReferent(new User())
            ->setService(new Service())
            ->setDevice(new Device());
    }

    public function testValidSupportGroup()
    {
        $this->assertHasErrors($this->supportGroup, 0);
    }

    public function testNullStatusSupportGroup()
    {
        $this->assertHasErrors($this->supportGroup->setStatus(null), 1);
    }

    protected function tearDown(): void
    {
        $this->supportGroup = null;
    }
}
