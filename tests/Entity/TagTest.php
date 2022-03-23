<?php

namespace App\Tests\Entity;

use App\Entity\Organization\Tag;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TagTest extends WebTestCase
{
    use AssertHasErrorsTrait;

    /** @var Tag */
    protected $tag;

    /** @var array */
    private $data;

    protected function setUp(): void
    {
        $this->tag = (new Tag())
            ->setName('Tag test')
        ;
    }

    public function testValidTag(): void
    {
        $this->assertHasErrors($this->tag->setName('Valid tag'), 0);
    }

    public function testBlankName(): void
    {
        $this->assertHasErrors($this->tag->setName(''), 1);
    }

    public function testGetName(): void
    {
        $this->assertEquals('Tag test', $this->tag->getName());
    }

    protected function tearDown(): void
    {
        $this->tag = null;
    }
}
