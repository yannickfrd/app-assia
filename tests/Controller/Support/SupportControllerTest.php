<?php

namespace App\Tests\Controller\Support;

use App\Entity\Organization\User;
use App\Entity\Support\SupportGroup;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\Response;

class SupportControllerTest extends WebTestCase
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

        $this->databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();
    }

    public function testSearchSupportsIsSuccessful(): void
    {
        $this->loadFixtures();

        $this->client->loginUser($this->fixtures['john_user']);

        $this->client->request('GET', '/supports');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Suivis');

        $this->client->submitForm('search', [
            'fullname' => 'John Doe',
            'date[start]' => '2018-01-01',
            'date[end]' => (new \DateTime())->format('Y-m-d'),
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Suivis');
    }

    public function testExportSupportsIsSuccessful(): void
    {
        $this->loadFixtures();

        $this->client->loginUser($this->fixtures['john_user']);

        $this->client->request('GET', '/supports');

        $this->client->submitForm('export', [
            'supportDates' => 1,
            'date[start]' => (new \DateTime())->modify('+1 year')->format('Y-m-d'),
        ], 'GET');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.alert.alert-warning', 'Aucun résultat à exporter.');

        $this->client->submitForm('export', [], 'GET');

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('.spreadsheetml.sheet', $this->client->getResponse()->headers->get('content-type'));
    }

    public function testNewSupportGroupAjax(): void
    {
        $this->loadFixtures();

        $this->client->loginUser($this->fixtures['john_user']);

        $id = $this->fixtures['people_group1']->getId();
        $this->client->request('GET', "/group/$id/new_support");

        $this->assertResponseIsSuccessful();
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('html', $content);
    }

    public function testNewSupportGroupPageIsUp(): void
    {
        $this->loadFixtures();

        $this->client->loginUser($this->fixtures['john_user']);

        $id = $this->fixtures['people_group2']->getId();
        $this->client->request('POST', "/people-group/$id/new-support", [
            'support' => ['service' => $this->fixtures['service1']->getId()],
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Nouveau suivi');
    }

    public function testCreateNewSupportGroupIsSuccessful(): void
    {
        $this->loadFixtures();

        $this->client->loginUser($user = $this->fixtures['john_user']);

        $id = $this->fixtures['people_group2']->getId();
        $this->client->request('POST', "/people-group/$id/new-support", [
            'support' => [
                'service' => $this->fixtures['service1'],
                'device' => $this->fixtures['device1']->getCode(),
                'referent' => $user,
            ],
        ]);

        $now = new \DateTime();
        $this->client->submitForm('send', [
            'support' => [
                'originRequest' => [
                    'organization' => $this->fixtures['organization1'],
                    'organizationComment' => 'XXX',
                    'preAdmissionDate' => $now->format('Y-m-d'),
                    'resulPreAdmission' => 1,
                    'decisionDate' => $now->format('Y-m-d'),
                    'comment' => 'XXX',
                ],
                'service' => $this->fixtures['service1'],
                'device' => $this->fixtures['device1']->getCode(),
                'status' => SupportGroup::STATUS_IN_PROGRESS,
                'referent' => $user,
                'startDate' => $now->format('Y-m-d'),
                'agreement' => true,
            ],
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.alert.alert-success');
    }

    public function testCreateNewSupportGroupAndCloneIsSuccessful(): void
    {
        $this->loadFixtures();

        $this->client->loginUser($this->fixtures['john_user']);

        $id = $this->fixtures['people_group2']->getId();
        $this->client->request('POST', "/people-group/$id/new-support", [
            'support' => ['service' => $this->fixtures['service1']->getId()],
        ]);

        $this->client->submitForm('send', [
            'support' => [
                'service' => $this->fixtures['service1'],
                'device' => $this->fixtures['device1']->getCode(),
                'status' => SupportGroup::STATUS_IN_PROGRESS,
                'agreement' => true,
                '_cloneSupport' => true,
            ],
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.alert.alert-success');
    }

    public function testPeopleHaveOtherSupportInProgress(): void
    {
        $this->loadFixtures();

        $this->client->loginUser($this->fixtures['john_user']);

        $id = $this->fixtures['people_group1']->getId();
        $this->client->request('POST', "/people-group/$id/new-support", [
            'support' => ['service' => $this->fixtures['service1']->getId()],
        ]);

        $this->client->submitForm('send', [
            'support[service]' => $this->fixtures['service1'],
            'support[device]' => $this->fixtures['device1']->getCode(),
            'support[status]' => SupportGroup::STATUS_IN_PROGRESS,
            'support[agreement]' => true,
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.alert.alert-danger', 'Attention, un suivi social est déjà en cours');
    }

    public function testCreateEndedSupportWithOtherSupportInProgressIsSuccessful(): void
    {
        $this->loadFixtures();

        $this->client->loginUser($this->fixtures['john_user']);

        $id = $this->fixtures['people_group1']->getId();
        $this->client->request('POST', "/people-group/$id/new-support", [
            'support' => ['service' => $this->fixtures['service1']->getId()],
        ]);

        $this->client->submitForm('send', [
            'support[service]' => $this->fixtures['service1'],
            'support[device]' => $this->fixtures['device1']->getCode(),
            'support[status]' => SupportGroup::STATUS_ENDED,
            'support[agreement]' => true,
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.alert.alert-success', 'Le suivi social est créé');
    }

    public function testEditSupportGroupIsSuccessful(): void
    {
        $this->loadFixtures();

        $this->client->loginUser($this->fixtures['john_user']);

        $id = $this->fixtures['support_group1']->getId();
        $this->client->request('GET', "/support/$id/edit");

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Édition du suivi');

        $this->client->submitForm('send');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.alert.alert-success');
    }

    public function testEditCoefficientIsSuccessful(): void
    {
        $this->loadFixtures();

        $this->client->loginUser($this->fixtures['user_admin']);

        $id = $this->fixtures['support_group2']->getId();
        $this->client->request('GET', "/support/$id/edit");

        $this->client->submitForm('send-coeff', [
            'support_coefficient[coefficient]' => 2,
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.alert.alert-success', 'Le coefficient du suivi est mis à jour.');
    }

    public function testShowSupportGroupIsUp(): void
    {
        $this->fixtures = $this->databaseTool->loadAliceFixture([
            dirname(__DIR__).'/../fixtures/app_fixtures_test.yaml',
            dirname(__DIR__).'/../fixtures/service_fixtures_test.yaml',
            dirname(__DIR__).'/../fixtures/person_fixtures_test.yaml',
            dirname(__DIR__).'/../fixtures/support_fixtures_test.yaml',
            dirname(__DIR__).'/../fixtures/evaluation_fixtures_test.yaml',
        ]);

        $this->client->loginUser($this->fixtures['john_user']);

        $id = $this->fixtures['support_group_with_eval']->getId();
        $this->client->request('GET', "/support/$id/show");

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Suivi social');
    }

    public function testDeleteSupportIsFailed(): void
    {
        $this->loadFixtures();

        $this->client->loginUser($this->fixtures['john_user']);

        $id = $this->fixtures['support_group1']->getId();
        $this->client->request('GET', "/support/$id/delete");

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testDeleteSupportIsSuccessful(): void
    {
        $this->loadFixtures();

        /** @var User $admin */
        $admin = $this->fixtures['user_admin'];
        $this->client->loginUser($admin);

        $id = $this->fixtures['support_group3']->getId();
        $this->client->request('GET', "/support/$id/delete");

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Groupe');
        $this->assertSelectorExists('.alert.alert-warning');
    }

    public function testRestoreSupportIsSuccessful(): void
    {
        $this->loadFixtures();

        /** @var User $admin */
        $admin = $this->fixtures['user_super_admin'];
        $this->client->loginUser($admin);

        /** @var SupportGroup $support */
        $support = $this->fixtures['support_group2'];

        $id = $support->getId();
        $this->client->request('GET', "/support/$id/delete");

        // After delete a support
        $crawler = $this->client->request('GET', '/supports', [
            'deleted' => ['deleted' => true],
        ]);
        $this->assertResponseIsSuccessful();
        $this->assertCount(1, $crawler->filter('tbody tr'));

        $id = $support->getSupportPeople()->first()->getId();
        $this->client->request('GET', "/support-person/$id/restore");
        $this->assertSelectorTextContains('.alert.alert-success', 'a bien été restauré');

        // After restore a support
        $crawler = $this->client->request('GET', '/supports', [
            'deleted' => ['deleted' => true],
        ]);
        $this->assertCount(0, $crawler->filter('tbody tr'));
    }

    public function testCloneSupportIsFailed(): void
    {
        $this->loadFixtures();

        $this->client->loginUser($this->fixtures['user_super_admin']);

        $id = $this->fixtures['support_group2']->getId();
        $this->client->request('GET', "/support/$id/clone");

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.alert.alert-warning', 'Aucun autre suivi n\'a été trouvé.');
    }

    public function testCloneSupportIsSuccessful(): void
    {
        $this->loadFixtures();

        $this->client->loginUser($this->fixtures['user_super_admin']);

        $id = $this->fixtures['support_group1']->getId();
        $this->client->request('GET', "/support/$id/clone");

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.alert.alert-success', 'Les informations du précédent suivi ont été ajoutées');
    }

    public function testShowSupportsWithContributioIsUp(): void
    {
        $this->loadFixtures();

        $this->client->loginUser($this->fixtures['john_user']);

        $this->client->request('GET', '/supports/current_month');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Suivis en présence');
    }

    public function testSwitchReferentPageIsForbidden(): void
    {
        $this->loadFixtures();

        $this->client->loginUser($this->fixtures['john_user']);

        $this->client->request('GET', '/supports/switch-referent');

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testSwitchReferentPageIsUp(): void
    {
        $this->loadFixtures();

        $this->client->loginUser($this->fixtures['user_admin']);

        $this->client->request('GET', '/supports/switch-referent');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Transfert de suivis');
    }

    public function testSwitchReferentIsSuccessful(): void
    {
        $this->loadFixtures();

        $this->client->loginUser($this->fixtures['user_admin']);

        $this->client->request('GET', '/supports/switch-referent');

        $this->client->submitForm('save', [
            '_oldReferent' => $this->fixtures['john_user'],
            '_newReferent' => $this->fixtures['user5'],
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.alert.alert-success', '3 suivis ont été transférés');
    }

    private function loadFixtures(): void
    {
        $this->fixtures = $this->databaseTool->loadAliceFixture([
            dirname(__DIR__).'/../fixtures/app_fixtures_test.yaml',
            dirname(__DIR__).'/../fixtures/service_fixtures_test.yaml',
            dirname(__DIR__).'/../fixtures/person_fixtures_test.yaml',
            dirname(__DIR__).'/../fixtures/support_fixtures_test.yaml',
        ]);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $cache = new FilesystemAdapter($_SERVER['DB_DATABASE_NAME']);
        $cache->clear();
    }
}
