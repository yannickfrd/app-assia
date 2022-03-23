<?php

namespace App\Tests\Entity;

use App\Entity\Support\Rdv;
use App\Tests\Entity\AssertHasErrorsTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RdvTest extends WebTestCase
{
    use AssertHasErrorsTrait;

    /** @var Rdv */
    protected $rdv;

    protected function setUp(): void
    {
        $faker = \Faker\Factory::create('fr_FR');
        $start = $faker->dateTimeBetween('-1 months', '+ 1 months');
        $end = $faker->dateTimeBetween($start, '+ 1 months');

        $this->rdv = (new Rdv())
            ->setTitle('RDV test')
            ->setContent($faker->paragraphs(6, true))
            ->setStart($start)
            ->setEnd($end)
            ->setStatus(1);
    }

    public function testValidRdv(): void
    {
        $this->assertHasErrors($this->rdv, 0);
    }

    public function testBlankTitle(): void
    {
        $this->assertHasErrors($this->rdv->setTitle(''), 1);
    }

    public function testNullStart(): void
    {
        $this->assertHasErrors($this->rdv->setStart(null), 1);
    }

    public function testNullEnd(): void
    {
        $this->assertHasErrors($this->rdv->setEnd(null), 1);
    }

    protected function tearDown(): void
    {
        $this->rdv = null;
    }
}
