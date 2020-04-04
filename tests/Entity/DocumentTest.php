<?php

namespace App\Tests\Entity;

use App\Entity\Document;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use App\Tests\Entity\AssertHasErrorsTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DocumentTest extends WebTestCase
{
    use FixturesTrait;
    use AssertHasErrorsTrait;

    /** @var Document */
    protected $document;


    protected function setUp()
    {
        $faker = \Faker\Factory::create("fr_FR");

        $this->document = (new Document())
            ->setName("Document 666")
            ->setType(1)
            ->setInternalFileName($faker->slug());
    }

    public function testValidDocument()
    {
        $this->assertHasErrors($this->document, 0);
    }

    public function testBlankName()
    {
        $this->assertHasErrors($this->document->setName(""), 1);
    }

    public function testNullType()
    {
        $this->assertHasErrors($this->document->setType(null), 1);
    }

    // protected function tearDown()
    // {
    //     $this->document = null;
    // }
}
