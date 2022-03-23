<?php

namespace App\Tests\Entity;

use App\Entity\People\Person;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PersonTest extends WebTestCase
{
    use AssertHasErrorsTrait;

    /** @var Person */
    protected $person;

    protected function setUp(): void
    {
        $faker = \Faker\Factory::create('fr_FR');

        $this->person = (new Person())
            ->setFirstName($faker->firstname())
            ->setLastName($faker->lastName())
            ->setGender(mt_rand(1, 3))
            ->setBirthdate($faker->dateTimeBetween($startDate = '-55 years', $endDate = '-18 years', $timezone = null))
            ->setEmail($faker->email());
    }

    public function testValidPerson()
    {
        $this->assertHasErrors($this->person, 0);
    }

    public function testBlankFirstname()
    {
        $this->assertHasErrors($this->person->setFirstname(''), 2);
    }

    public function testInvalidFirstname()
    {
        $this->assertHasErrors($this->person->setFirstname('x'), 1);
    }

    public function testBlankLastname()
    {
        $this->assertHasErrors($this->person->setLastname(''), 2);
    }

    public function testInvalidLastname()
    {
        $this->assertHasErrors($this->person->setLastname('x'), 1);
    }

    public function testInvalidEmail()
    {
        $this->assertHasErrors($this->person->setEmail('xxxx@xxx'), 1);
    }

    public function testPersonExists()
    {
        /** @var AbstractDatabaseTool */
        $databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();

        $databaseTool->loadAliceFixture([
            dirname(__DIR__).'/DataFixturesTest/UserFixturesTest.yaml',
            dirname(__DIR__).'/DataFixturesTest/PersonFixturesTest.yaml',
        ]);

        $person = $this->person
            ->setFirstname('John')
            ->setLastname('Doe')
            ->setBirthdate(new \DateTime('1980-01-01'));
        $this->assertHasErrors($person, 1);
    }

    protected function tearDown(): void
    {
        $this->person = null;
    }
}
