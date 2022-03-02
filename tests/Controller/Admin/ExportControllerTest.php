<?php

namespace App\Tests\Controller\Admin;

use App\Entity\Support\SupportGroup;
use App\Tests\AppTestTrait;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class ExportControllerTest extends WebTestCase
{
    use AppTestTrait;

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

        $this->client = $this->createClient();

        /* @var AbstractDatabaseTool */
        $this->databaseTool = $this->getContainer()->get(DatabaseToolCollection::class)->get();

        $this->fixtures = $this->databaseTool->loadAliceFixture([
            dirname(__DIR__).'/../DataFixturesTest/UserFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/ServiceFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/PersonFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/SupportFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/EvaluationFixturesTest.yaml',
        ]);

        $this->createLogin($this->fixtures['userSuperAdmin']);
    }

    public function testExportIsSuccessful()
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
        $this->assertSame(Response::HTTP_NOT_FOUND, $this->client->getResponse()->getStatusCode());

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

    public function testCountResultsIsSuccessful()
    {
        $this->client->request('POST', '/export/count', $this->getFormData());

        $this->assertResponseIsSuccessful();
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('count', $content['action']);
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

        $this->client = null;
        $this->fixtures = null;
    }
}
