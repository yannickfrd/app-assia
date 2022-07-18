<?php

namespace App\Tests\Controller\Api;

use App\Entity\Support\SupportGroup;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @group api
 */
class SiSiaoControllerTest extends WebTestCase
{
    /** @var KernelBrowser */
    protected $client;

    /** @var array */
    protected $fixtures;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();
        $this->client->followRedirects();

        $this->databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();

        $this->fixtures = $this->databaseTool->loadAliceFixture([
            dirname(__DIR__).'/../fixtures/app_fixtures_test.yaml',
        ]);

        $this->siSiaoGroupId = $_SERVER['SISIAO_GROUP_ID'];
    }

    public function testSiSiaoLoginPageIsUp(): void
    {
        $this->client->loginUser($this->fixtures['john_user']);

        $this->client->request('GET', '/api-sisiao/login');

        $this->assertResponseIsSuccessful();
    }

    public function testSiSiaoLoginIsFailed(): void
    {
        $this->client->loginUser($this->fixtures['john_user']);
        $this->loginToSiSiao('bad_username');

        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testSiSiaoLoginIsSuccesful(): void
    {
        $this->client->loginUser($this->fixtures['john_user']);

        $responseContent = $this->loginToSiSiao();

        $this->assertResponseIsSuccessful();
        $this->assertSame('success', $responseContent['alert']);
    }

    public function testCheckConnectionIsFailed(): void
    {
        $this->client->loginUser($this->fixtures['john_user']);
        $this->loginToSiSiao();

        $this->client->request('GET', '/api-sisiao/check-connection');

        $responseContent = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertResponseIsSuccessful();
        $this->assertSame(true, $responseContent['isConnected']);
    }

    public function testCheckConnectionIsSuccessful(): void
    {
        $this->client->loginUser($this->fixtures['john_user']);

        $this->client->request('GET', '/api-sisiao/check-connection');

        $responseContent = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertResponseIsSuccessful();
        $this->assertSame(false, $responseContent['isConnected']);
    }

    public function testSearchGroupIsSuccessful(): void
    {
        $this->client->loginUser($this->fixtures['john_user']);
        $this->loginToSiSiao();

        $this->client->request('GET', "/api-sisiao/search-group/{$this->siSiaoGroupId}");

        $responseContent = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertResponseIsSuccessful();
        $this->assertArrayHasKey('people', $responseContent);
    }

    public function testShowGroupIsFailed(): void
    {
        $this->client->loginUser($this->fixtures['john_user']);
        $this->loginToSiSiao();

        // Try to show group with unknown ID (666)
        $this->client->request('GET', '/api-sisiao/show-group/666');

        $responseContent = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertResponseIsSuccessful();
        $this->assertSame('warning', $responseContent['alert']);
    }

    public function testShowGroupIsSuccessful(): void
    {
        $this->client->loginUser($this->fixtures['john_user']);
        $this->loginToSiSiao();

        $this->client->request('GET', "/api-sisiao/show-group/{$this->siSiaoGroupId}");

        $responseContent = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertResponseIsSuccessful();
        $this->assertSame('success', $responseContent['alert']);
    }

    public function testImportGroupIsFailed(): void
    {
        $this->client->loginUser($this->fixtures['john_user']);
        $this->loginToSiSiao();

        // Try to import group with unknown ID (666)
        $this->client->request('GET', '/api-sisiao/import-group/666');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.toast.alert-warning', "Il n'y a pas de dossier SI-SIAO correspondant avec la clé « 666 »");
    }

    public function testImportGroupIsSuccessful(): void
    {
        $this->client->loginUser($this->fixtures['john_user']);
        $this->loginToSiSiao();

        $this->client->request('GET', "/api-sisiao/import-group/{$this->siSiaoGroupId}");

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.toast.alert-success', 'Le groupe a été importé');
    }

    public function testImportEvaluationIsSuccessful(): void
    {
        $this->client->loginUser($this->fixtures['john_user']);
        $this->loginToSiSiao();

        $this->client->request('GET', "/api-sisiao/import-group/{$this->siSiaoGroupId}");

        $this->assertResponseIsSuccessful();

        $this->createSupport();

        $this->client->request('GET', '/api-sisiao/support/1/import-evaluation');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.toast.alert-success', "L'évaluation sociale a été importée");

        $this->client->request('GET', '/api-sisiao/support/1/import-evaluation');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.toast.alert-success', "L'évaluation sociale a été mise à jour");
    }

    public function testGetUserIsSuccessful(): void
    {
        $this->client->loginUser($this->fixtures['user_super_admin']);
        $this->loginToSiSiao();

        $this->client->request('GET', '/api-sisiao/user');

        $responseContent = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertResponseIsSuccessful();
        $this->assertSame('ACTIF', $responseContent['etat']);
    }

    public function testGetReferentiels(): void
    {
        $this->client->loginUser($this->fixtures['user_super_admin']);
        $this->loginToSiSiao();

        $this->client->request('GET', '/api-sisiao/referentiels');

        $this->assertResponseIsSuccessful();
    }

    public function testLogoutIsSuccessful(): void
    {
        $this->client->loginUser($this->fixtures['john_user']);
        $this->loginToSiSiao();

        $this->client->request('GET', '/api-sisiao/logout');

        $responseContent = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertResponseIsSuccessful();
        $this->assertSame('success', $responseContent['alert']);
    }

    private function loginToSiSiao(?string $username = null, ?string $password = null): array
    {
        $crawler = $this->client->request('GET', '/new_support/search/person');

        $this->client->request('POST', '/api-sisiao/login-ajax', [
            'si_siao_login' => [
                'username' => $username ?? $_SERVER['SISIAO_LOGIN'],
                'password' => $password ?? $_SERVER['SISIAO_PASSWORD'],
                '_token' => $crawler->filter('#si_siao_login__token')->attr('value'),
            ],
        ]);

        return json_decode($this->client->getResponse()->getContent(), true);
    }

    private function createSupport(): void
    {
        $crawler = $this->client->request('POST', '/people-group/1/new-support', [
            'support' => [
                'service' => $service = $this->fixtures['service1']->getId(),
                'device' => $device = $this->fixtures['device1']->getCode(),
            ],
        ]);

        $this->client->submitForm('send', [
            'support' => [
                'service' => $service,
                'device' => $device,
                'status' => SupportGroup::STATUS_IN_PROGRESS,
                'startDate' => (new \DateTime())->format('Y-m-d'),
                'agreement' => true,
                '_token' => $crawler->filter('#support__token')->attr('value'),
            ],
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.toast.alert-success', 'Le suivi a été créé');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
