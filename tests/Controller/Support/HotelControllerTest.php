<?php

namespace App\Tests\Controller\Support;

use App\Entity\Support\SupportGroup;
use App\Tests\AppTestTrait;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Response;

class HotelControllerTest extends WebTestCase
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
            dirname(__DIR__).'/../DataFixturesTest/HotelSupportFixturesTest.yaml',
        ]);

        $this->supportGroup = $this->data['supportGrpHotel1'];
    }

    public function testSearchHotelSupportsIsSuccessful()
    {
        $this->createLogin($this->data['userRoleUser']);

        // Page is up
        $this->client->request('GET', '/hotel-supports');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Suivis PASH');

        // Search is successful
        /** @var Crawler */
        $crawler = $this->client->submitForm('search', [], 'GET');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Suivis PASH');
        $this->assertGreaterThanOrEqual(2, $crawler->filter('tr')->count());
    }

    public function testExportHotelSupportsIsSuccessful()
    {
        $this->createLogin($this->data['userRoleUser']);

        $this->client->request('GET', '/hotel-supports');

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

    public function testCreateHotelSupportGroupIsSuccessful()
    {
        $user = $this->data['userRoleUser'];
        $this->createLogin($user);

        $id = $this->data['peopleGroup3']->getId();
        $this->client->request('POST', "/group/$id/support/new", [
            'support' => [
                'service' => $this->data['servicePash'],
                'device' => $this->data['deviceHotel'],
                'referent' => $user,
            ],
        ]);

        $now = new \DateTime();
        $this->client->submitForm('send', [
            'support' => [
                'service' => $this->data['servicePash'],
                'device' => $this->data['deviceHotel'],
                'status' => SupportGroup::STATUS_IN_PROGRESS,
                'referent' => $user,
                'originRequest' => [
                    'orientationDate' => $now->format('Y-m-d'),
                    'organization' => $this->data['siao95'],
                    'comment' => 'XXX',
                ],
                'hotelSupport' => [
                    'entryHotelDate' => '2020-01-01',
                ],
                'startDate' => $now->format('Y-m-d'),
                'agreement' => true,
            ],
        ]);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('.alert.alert-success', 'Le suivi social est créé.');
    }

    public function testEditHotelSupportGroupIsSuccessful()
    {
        $user = $this->data['userRoleUser'];
        $this->createLogin($this->data['userRoleUser']);

        $id = $this->data['supportGrpHotel1']->getId();
        $this->client->request('GET', "/support/$id/edit");

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Édition du suivi');

        $now = new \DateTime();
        $this->client->submitForm('send', [
            'support' => [
                'service' => $this->data['servicePash'],
                'subService' => $this->data['subServicePash'],
                'device' => $this->data['deviceHotel'],
                'status' => SupportGroup::STATUS_IN_PROGRESS,
                'referent' => $user,
                'originRequest' => [
                    'orientationDate' => $now->format('Y-m-d'),
                    'organization' => $this->data['siao95'],
                    'comment' => 'XXX',
                ],
                'hotelSupport' => [
                    'entryHotelDate' => '2020-01-01',
                    'emergencyActionRequest' => 1,
                    'reasonNoInclusion' => 1,
                    'evaluationDate' => $now->format('Y-m-d'),
                    'levelSupport' => 1,
                    'agreementDate' => $now->format('Y-m-d'),
                    'emergencyActionDone' => 1,
                    'emergencyActionPrecision' => 'XXX',
                    'departmentAnchor' => 95,
                    'recommendation' => 10,
                    'endSupportReason' => 1,
                ],
                'startDate' => $now->format('Y-m-d'),
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
