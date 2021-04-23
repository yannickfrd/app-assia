<?php

namespace App\Tests\Controller\Support;

use App\Tests\AppTestTrait;
use App\Entity\Support\SupportGroup;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Response;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class AvdlControllerTest extends WebTestCase
{
    use FixturesTrait;
    use AppTestTrait;

    /** @var KernelBrowser */
    protected $client;

    /** @var array */
    protected $data;

    /** @var SupportGroup */
    protected $supportGroup;

    protected function setUp()
    {
        $this->data = $this->loadFixtureFiles([
            dirname(__DIR__).'/../DataFixturesTest/UserFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/ServiceFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/PersonFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/AvdlSupportFixturesTest.yaml',
        ]);

        $this->supportGroup = $this->data['supportGrpAvdl1'];
    }

    public function testSearchAvdlSupportsIsSuccessful()
    {
        $this->createLogin($this->data['userRoleUser']);

        $this->client->request('GET', '/avdl-supports');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Suivis AVDL');

        /** @var Crawler */
        $crawler = $this->client->submitForm('search', [
            'diagOrSupport' => 1,
            'supportType' => [1, 2],
        ], 'GET');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Suivis AVDL');
        $this->assertGreaterThanOrEqual(2, $crawler->filter('tr')->count());
    }

    public function testExportAvdlSupportsIsSuccessful()
    {
        $this->createLogin($this->data['userRoleUser']);

        $this->client->request('GET', '/avdl-supports');

        // Export without result
        $this->client->submitForm('export', [
            'supportDates' => 1,
            'date[start]' => (new \DateTime())->modify('+1 year')->format('Y-m-d'),
        ], 'GET');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('.alert.alert-warning', 'Aucun résultat à exporter.');

        // Export with results
        $this->client->submitForm('export', [], 'GET');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertContains('.spreadsheetml.sheet', $this->client->getResponse()->headers->get('content-type'));
    }

    public function testCreateAvdlSupportGroupIsSuccessful()
    {
        $user = $this->data['userRoleUser'];
        $this->createLogin($user);

        $id = $this->data['peopleGroup3']->getId();
        $this->client->request('POST', "/group/$id/support/new", [
            'support' => [
                'service' => $this->data['serviceAvdl'],
                'device' => $this->data['deviceAvdl'],
                'referent' => $user,
            ],
        ]);

        $now = new \DateTime();
        $this->client->submitForm('send', [
            'support' => [
                'service' => $this->data['serviceAvdl'],
                'device' => $this->data['deviceAvdl'],
                'status' => SupportGroup::STATUS_IN_PROGRESS,
                'referent' => $user,
                'originRequest' => [
                    'orientationDate' => $now->format('Y-m-d'),
                    'organization' => $this->data['comedDalo'],
                ],
                'avdl' => [
                    'diagStartDate' => '2020-01-01',
                    'diagType' => 1,
                    // 'supportStartDate' => $now->format('Y-m-d'),
                ],
                'agreement' => true,
            ],
        ]);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        // dump($this->client->getResponse()->getContent());
        $this->assertSelectorTextContains('.alert.alert-success', 'Le suivi social est créé.');
    }

    public function testEditAvdlSupportGroupIsSuccessful()
    {
        $user = $this->data['userRoleUser'];
        $this->createLogin($this->data['userRoleUser']);

        $id = $this->data['supportGrpAvdl1']->getId();
        $this->client->request('GET', "/support/$id/edit");

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Édition du suivi');

        $now = new \DateTime();
        $this->client->submitForm('send', [
            'support' => [
                'service' => $this->data['serviceAvdl'],
                'device' => $this->data['deviceAvdl'],
                'status' => SupportGroup::STATUS_IN_PROGRESS,
                'referent' => $user,
                'originRequest' => [
                    'orientationDate' => $now->format('Y-m-d'),
                    'organization' => $this->data['comedDalo'],
                    'comment' => 'XXX',
                ],
                'avdl' => [
                    'diagStartDate' => '2020-01-01',
                    'diagType' => 1,
                    'recommendationSupport' => 1,
                    'diagEndDate' => '2020-09-30',
                    'diagComment' => 'XXX',
                    'supportStartDate' => '2020-10-01',
                    'supportType' => 3,
                    'supportEndDate' => $now->format('Y-m-d'),
                    'propoHousingDate' => '2021-03-19',
                    'accessHousingModality' => 2,
                ],
                'agreement' => true,
            ],
        ]);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('.alert.alert-success', 'Le suivi social est mis à jour.');

        $this->client->request('GET', "/support/$id/view");
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->client = null;
        $this->data = null;

        $cache = new FilesystemAdapter($_SERVER['DB_DATABASE_NAME']);
        $cache->clear();

    }
}
