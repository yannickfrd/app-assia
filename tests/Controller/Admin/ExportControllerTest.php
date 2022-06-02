<?php

namespace App\Tests\Controller\Admin;

use App\Entity\Support\SupportGroup;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class ExportControllerTest extends WebTestCase
{
    /** @var KernelBrowser */
    protected $client;

    /** @var AbstractDatabaseTool */
    protected $databaseTool;

    /** @var array */
    protected $fixtures;

    /** @var SupportGroup */
    protected $supportGroup;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();
        $this->client->followRedirects();

        $this->databaseTool = $this->getContainer()->get(DatabaseToolCollection::class)->get();

        $this->fixtures = $this->databaseTool->loadAliceFixture([
            dirname(__DIR__).'/../fixtures/app_fixtures_test.yaml',
            dirname(__DIR__).'/../fixtures/service_fixtures_test.yaml',
            dirname(__DIR__).'/../fixtures/person_fixtures_test.yaml',
            dirname(__DIR__).'/../fixtures/support_fixtures_test.yaml',
            dirname(__DIR__).'/../fixtures/evaluation_fixtures_test.yaml',
        ]);

        $this->client->loginUser($this->fixtures['user_super_admin']);
    }

    public function testExportIsSuccessful(): void
    {
        $this->client->request('GET', '/exports');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Export des données');

        $this->client->request('POST', '/export/new', $this->getFormData());

        $this->assertResponseIsSuccessful();
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('create', $content['action']);

        $id = $content['export']['id'];
        $this->client->request('POST', "/export/$id/send", $this->getFormData());

        $this->assertResponseIsSuccessful();
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('export', $content['action']);

        // Fail to get the export
        $this->client->request('GET', '/export/2/download');
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);

        // Success to get the export
        $this->client->request('GET', '/export/1/download');

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('spreadsheetml.sheet', $this->client->getResponse()->headers->get('content-type'));

        // Delete the export
        $this->client->request('GET', '/export/1/delete');

        $this->assertResponseIsSuccessful();
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('delete', $content['action']);
    }

    public function testCountResultsIsSuccessful(): void
    {
        $this->client->request('POST', '/export/count', $this->getFormData());

        $this->assertResponseIsSuccessful();
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('count', $content['action']);
    }

    public function testDownloadModel(): void
    {
        $this->client->request('GET', '/export/download-model');

        $this->assertResponseIsSuccessful();
    }

    private function getFormData(): array
    {
        return [
            'supportDates' => '',
            'date' => [
                'start' => '',
                'end' => '',
            ],
            'head' => false,
            'status' => [SupportGroup::STATUS_IN_PROGRESS],
        ];
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
