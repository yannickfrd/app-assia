<?php

namespace App\Tests\Entity;

use App\Entity\Note;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use App\Tests\Entity\AssertHasErrorsTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class NoteTest extends WebTestCase
{
    use FixturesTrait;
    use AssertHasErrorsTrait;

    /** @var Note */
    protected $note;


    protected function setUp()
    {
        $faker = \Faker\Factory::create("fr_FR");

        $this->note = (new Note())
            ->setTitle("Note 666")
            ->setContent($faker->paragraphs(6, true))
            ->setType(1)
            ->setStatus(1);
    }

    public function testValidNote()
    {
        $this->assertHasErrors($this->note, 0);
    }

    public function testBlankContent()
    {
        $this->assertHasErrors($this->note->setContent(""), 1);
    }

    // protected function tearDown()
    // {
    //     $this->note = null;
    // }
}
