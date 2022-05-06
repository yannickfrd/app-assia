<?php

namespace App\Tests\Command\Event;

use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class SendTaskAlertsCommandTest extends KernelTestCase
{
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
            dirname(__DIR__).'/../fixtures/app_fixtures_test.yaml',
            dirname(__DIR__).'/../fixtures/service_fixtures_test.yaml',
            dirname(__DIR__).'/../fixtures/person_fixtures_test.yaml',
            dirname(__DIR__).'/../fixtures/support_fixtures_test.yaml',
            dirname(__DIR__).'/../fixtures/task_fixtures_test.yaml',
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

        $this->assertStringContainsString('emails were sent!', $output);
    }

    public function testExecuteWithFlushOption(): void
    {
        $this->commandTester->execute([
            'command' => $this->command->getName(),
            'notif-type' => '+30 days',
            '--flush' => true,
        ]);

        $output = $this->commandTester->getDisplay();

        $this->assertStringContainsString('emails were sent!', $output);
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
    }
}
