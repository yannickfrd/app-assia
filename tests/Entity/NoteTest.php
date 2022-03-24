<?php

namespace App\Tests\Entity;

use App\Entity\Support\Note;
use App\Tests\Entity\AssertHasErrorsTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class NoteTest extends WebTestCase
{
    use AssertHasErrorsTrait;

    /** @var Note */
    protected $note;

    protected function setUp(): void
    {
        $faker = \Faker\Factory::create('fr_FR');

        $this->note = (new Note())
            ->setTitle('Note test')
            ->setContent($faker->paragraphs(6, true))
            ->setType(1)
            ->setStatus(1);
    }

    public function testValidNote(): void
    {
        $this->assertHasErrors($this->note, 0);
    }

    public function testBlankContent(): void
    {
        $this->assertHasErrors($this->note->setContent(''), 1);
    }

    protected function tearDown(): void
    {
        $this->note = null;
    }
}
