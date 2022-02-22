<?php

namespace App\Tests\Command\Event;

use App\Tests\AppTestTrait;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class SendTaskAlertsCommandTest extends KernelTestCase
{
    use AppTestTrait;

    /** @var Command */
    protected $command;

    /** @var CommandTester */
    protected $commandTester;

    protected function setUp(): void
    {
        parent::setUp();

        $kernel = self::bootKernel();

        $this->databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();

        $this->databaseTool->loadAliceFixture([
            dirname(__DIR__).'/../DataFixturesTest/UserFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/ServiceFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/PersonFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/SupportFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/task_fixtures_test.yaml',
        ]);

        $application = new Application($kernel);
        $this->command = $application->find('app:task:send-task-alerts');
        $this->commandTester = new CommandTester($this->command);
    }

    /** @dataProvider provideArgument */
    public function testExecuteIsSuccessful(string $argument): void
    {
        $this->commandTester->execute([
            'command' => $this->command->getName(),
            'notif-type' => $argument,
        ]);

        $output = $this->commandTester->getDisplay();

        $this->assertStringContainsString('emails were sended!', $output);
    }

    public function testExecuteWithFlushOption(): void
    {
        $this->commandTester->execute([
            'command' => $this->command->getName(),
            'notif-type' => '+30 days',
            '--flush' => true,
        ]);

        $output = $this->commandTester->getDisplay();

        $this->assertStringContainsString('emails were sended!', $output);
    }

    public function provideArgument(): \Generator
    {
        yield ['weekly-alerts'];
        yield ['daily-alerts'];
        yield ['+5 minutes'];
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->command = null;
        $this->commandTester = null;
    }
}
