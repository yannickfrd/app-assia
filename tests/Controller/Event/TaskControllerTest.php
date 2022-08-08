<?php

namespace App\Tests\Controller\Event;

use App\Entity\Event\Task;
use App\Entity\Support\SupportGroup;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TaskControllerTest extends WebTestCase
{
    /** @var KernelBrowser */
    protected $client;

    /** @var array */
    protected $fixtures;

    /** @var SupportGroup */
    protected $supportGroup;

    /** @var Task */
    protected $task;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();
        $this->client->followRedirects();

        $this->databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();

        $this->fixtures = $this->databaseTool->loadAliceFixture([
            dirname(__DIR__).'/../fixtures/app_fixtures_test.yaml',
            dirname(__DIR__).'/../fixtures/service_fixtures_test.yaml',
            dirname(__DIR__).'/../fixtures/person_fixtures_test.yaml',
            dirname(__DIR__).'/../fixtures/support_fixtures_test.yaml',
            dirname(__DIR__).'/../fixtures/task_fixtures_test.yaml',
        ]);

        $this->user = $this->fixtures['john_user'];
        $this->supportGroup = $this->fixtures['support_group1'];
        $this->task = $this->fixtures['task1'];
    }

    public function testSearchTasksIsSuccessful(): void
    {
        $this->client->loginUser($this->fixtures['john_user']);

        $this->client->request('GET', '/tasks');

        // Page is up
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Tâches');

        // Search is successful
        $this->client->submitForm('search', [
            'date[end]' => (new \DateTime())->format('Y-m-d'),
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Tâches');
    }

    public function testExportTasksIsSuccessful(): void
    {
        $this->client->loginUser($this->fixtures['john_user']);

        $this->client->request('GET', '/tasks');

        // Export with no result
        $this->client->submitForm('export', [
            'date[start]' => (new \DateTime())->modify('+10 year')->format('Y-m-d'),
        ], 'GET');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.toast.alert-warning', 'Aucun résultat à exporter');

        // Export with results
        $this->client->submitForm('export', [], 'GET');

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('spreadsheetml.sheet', $this->client->getResponse()->headers->get('content-type'));
    }

    public function testSupportTasksIndexIsUp(): void
    {
        $this->client->loginUser($this->fixtures['john_user']);

        $this->client->request('GET', "/support/{$this->supportGroup->getId()}/tasks");

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Tâches');
    }

    public function testExportSupportTasksIsSuccessful(): void
    {
        $this->client->loginUser($this->fixtures['john_user']);

        $id = $this->supportGroup->getId();
        $this->client->request('GET', "/support/$id/tasks");

        $this->client->submitForm('export', [], 'GET');

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('spreadsheetml.sheet', $this->client->getResponse()->headers->get('content-type'));
    }

    public function testCreateNewTaskIsSuccessful(): void
    {
        $this->client->loginUser($this->fixtures['john_user']);

        $crawler = $this->client->request('GET', '/tasks');
        $csrfToken = $crawler->filter('#task__token')->attr('value');
        $now = new \DateTime();

        // Fail
        $this->client->request('POST', '/task/create');

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertSame('danger', $content['alert']);

        // Success
        $this->client->request('POST', '/task/create', [
            'task' => [
                'title' => 'Task test',
                'level' => Task::MEDIUM_LEVEL,
                'users' => [$this->user->getId()],
                'supportGroup' => $this->supportGroup->getId(),
                'end' => (clone $now)->modify('+1 hour')->format('Y-m-d\TH:00'),
                '_token' => $csrfToken,
            ],
        ]);

        $this->assertResponseIsSuccessful();
        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertSame('create', $content['action']);
        $this->assertSame('Task test', $content['task']['title']);
    }

    public function testCreateNewTaskWithSupportIsSuccessful(): void
    {
        $this->client->loginUser($this->fixtures['john_user']);

        $id = $this->supportGroup->getId();

        $crawler = $this->client->request('GET', "/support/$id/tasks");

        $this->assertResponseIsSuccessful();

        $csrfToken = $crawler->filter('#task__token')->attr('value');

        // // Fail
        $this->client->request('POST', "/support/$id/task/create");

        $this->assertResponseIsSuccessful();
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('danger', $content['alert']);

        // Success
        $this->client->request('POST', "/support/$id/task/create", [
            'task' => [
                'title' => 'Task test',
                'level' => Task::MEDIUM_LEVEL,
                'users' => [$this->user->getId()],
                'supportGroup' => $this->supportGroup->getId(),
                'end' => (clone new \DateTime())->modify('+1 hour')->format('Y-m-d\TH:00'),
                '_token' => $csrfToken,
            ],
        ]);

        $this->assertResponseIsSuccessful();
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('create', $content['action']);
        $this->assertSame('Task test', $content['task']['title']);
    }

    public function testShowTaskIsSuccessful(): void
    {
        $this->client->loginUser($this->fixtures['john_user']);

        $id = $this->task->getId();
        $this->client->request('GET', "/task/$id/show");
        $this->assertResponseIsSuccessful();

        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('show', $content['action']);
    }

    public function testEditTaskIsSuccessful(): void
    {
        $this->client->loginUser($this->fixtures['john_user']);

        $crawler = $this->client->request('GET', '/tasks');
        $id = $this->task->getId();

        // Fail
        $this->client->request('POST', "/task/$id/edit");

        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('danger', $content['alert']);

        // Success
        $this->client->request('POST', "/task/$id/edit", [
            'task' => [
                'title' => 'Task edit',
                'content' => $this->task->getContent(),
                'level' => Task::MEDIUM_LEVEL,
                'users' => [$this->user->getId()],
                'supportGroup' => $this->supportGroup->getId(),
                'end' => (clone new \DateTime())->modify('+1 hour')->format('Y-m-d\TH:00'),
                '_token' => $crawler->filter('#task__token')->attr('value'),
            ],
        ]);
        $this->assertResponseIsSuccessful();

        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('edit', $content['action']);
        $this->assertSame('Task edit', $content['task']['title']);
    }

    public function testDeleteTaskIsSuccessful(): void
    {
        $this->client->loginUser($this->fixtures['john_user']);

        $this->client->request('DELETE', "/task/{$this->task->getId()}/delete");

        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('delete', $content['action']);
    }

    public function testRestoreTaskIsSuccessful(): void
    {
        $this->client->loginUser($this->fixtures['user_super_admin']);

        $taskId = $this->task->getId();
        $this->client->request('DELETE', "/task/$taskId/delete");

        // After delete a task
        $id = $this->supportGroup->getId();
        $crawler = $this->client->request('GET', "/support/$id/tasks", [
            'deleted' => ['deleted' => true],
        ]);
        $this->assertResponseIsSuccessful();
        $this->assertSame(1, $crawler->filter('tbody tr')->count());

        $this->client->request('GET', "/task/$taskId/restore");
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('restore', $content['action']);

        // After restore a task
        $crawler = $this->client->request('GET', "/support/$id/tasks", [
            'deleted' => ['deleted' => true],
        ]);
        $this->assertSame(0, $crawler->filter('tbody tr')->count());
    }

    public function testToggleStatusIsSuccessful(): void
    {
        $this->client->loginUser($this->fixtures['john_user']);

        $this->client->request('GET', "/task/{$this->task->getId()}/toggle-status");

        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('toggle_status', $content['action']);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
