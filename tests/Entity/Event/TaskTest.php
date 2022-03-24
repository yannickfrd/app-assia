<?php

namespace App\Tests\Entity\Event;

use App\Entity\Event\Task;
use App\Entity\Organization\User;
use App\Tests\Entity\AssertHasErrorsTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TaskTest extends WebTestCase
{
    use AssertHasErrorsTrait;

    /** @var Task */
    protected $task;

    /** @var User */
    protected $user;

    protected function setUp(): void
    {
        $faker = \Faker\Factory::create('fr_FR');
        $start = $faker->dateTimeBetween('-1 month', '+ 1 month');
        $end = $faker->dateTimeBetween($start, '+ 1 month');

        $this->task = (new Task())
            ->setLevel(Task::MEDIUM_LEVEL)
            ->setTitle('Task test')
            ->setStart($start)
            ->setEnd($end)
            ->addUser($this->user)
            ->setSupportGroup(null)
            ->setContent($faker->paragraphs(6, true))
            ->setStatus(Task::TASK_IS_NOT_DONE)
        ;
    }

    public function testValidTask(): void
    {
        $this->assertHasErrors($this->task, 0);
    }

    public function testBlankTitle(): void
    {
        $this->assertHasErrors($this->task->setTitle(''), 1);
    }

    public function testNullStart(): void
    {
        $this->assertHasErrors($this->task->setStart(null), 0);
    }

    public function testNullEnd(): void
    {
        $this->assertHasErrors($this->task->setEnd(null), 1);
    }

    protected function tearDown(): void
    {
        $this->task = null;
    }
}
