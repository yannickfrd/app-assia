<?php

namespace App\Tests\Entity;

use App\Entity\Support\Rdv;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RdvTest extends WebTestCase
{
    use FixturesTrait;
    use AssertHasErrorsTrait;

    /** @var Rdv */
    protected $rdv;

    protected function setUp()
    {
        $faker = \Faker\Factory::create('fr_FR');
        $start = $faker->dateTimeBetween('-1 months', '+ 1 months');
        $end = $faker->dateTimeBetween($start, '+ 1 months');

        $this->rdv = (new Rdv())
            ->setTitle('Rdv 666')
            ->setContent($faker->paragraphs(6, true))
            ->setStart($start)
            ->setEnd($end)
            ->setStatus(1);
    }

    public function testValidRdv()
    {
        $this->assertHasErrors($this->rdv, 0);
    }

    public function testBlankTitle()
    {
        $this->assertHasErrors($this->rdv->setTitle(''), 1);
    }

    public function testNullStart()
    {
        $this->assertHasErrors($this->rdv->setStart(null), 1);
    }

    public function testNullEnd()
    {
        $this->assertHasErrors($this->rdv->setEnd(null), 1);
    }

    protected function tearDown(): void
    {
        $this->rdv = null;
    }
}
