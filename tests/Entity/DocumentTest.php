<?php

namespace App\Tests\Entity;

use App\Entity\Support\Document;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DocumentTest extends WebTestCase
{
    use AssertHasErrorsTrait;

    /** @var Document */
    protected $document;

    protected function setUp(): void
    {
        $faker = \Faker\Factory::create('fr_FR');

        $this->document = (new Document())
            ->setName('Document test')
            ->setType(1)
            ->setInternalFileName($faker->slug());
    }

    public function testValidDocument(): void
    {
        $this->assertHasErrors($this->document, 0);
    }

    public function testBlankName(): void
    {
        $this->assertHasErrors($this->document->setName(''), 1);
    }

    public function testNullType(): void
    {
        $this->assertHasErrors($this->document->setType(null), 0);
    }

    protected function tearDown(): void
    {
        $this->document = null;
    }
}
