<?php

namespace App\Tests\Controller\Support;

use App\Entity\Support\SupportGroup;
use App\Service\Grammar;
use App\Tests\AppTestTrait;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Response;

class SupportControllerTest extends WebTestCase
{
    use FixturesTrait;
    use AppTestTrait;

    /** @var KernelBrowser */
    protected $client;

    /** @var array */
    protected $data;

    /** @var SupportGroup */
    protected $supportGroup;

    protected function setUp(): void
    {
        $this->data = $this->loadFixtureFiles([
            dirname(__DIR__).'/../DataFixturesTest/UserFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/ServiceFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/PersonFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/SupportFixturesTest.yaml',
        ]);
    }

    public function testSearchSupportsIsSuccessful()
    {
        $this->createLogin($this->data['userRoleUser']);

        $this->client->request('GET', '/supports');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Suivis');

        $this->client->submitForm('search', [
            'fullname' => 'John Doe',
            'date[start]' => '2018-01-01',
            'date[end]' => (new \DateTime())->format('Y-m-d'),
        ]);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Suivis');
    }

    public function testExportSupportsIsSuccessful()
    {
        $this->createLogin($this->data['userRoleUser']);

        $this->client->request('GET', '/supports');

        $this->client->submitForm('export', [
            'supportDates' => 1,
            'date[start]' => (new \DateTime())->modify('+1 year')->format('Y-m-d'),
        ], 'GET');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('.alert.alert-warning', 'Aucun résultat à exporter.');

        $this->client->submitForm('export', [], 'GET');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertContains('.spreadsheetml.sheet', $this->client->getResponse()->headers->get('content-type'));
    }

    public function testNewSupportGroupAjax()
    {
        $this->createLogin($this->data['userRoleUser']);

        $id = $this->data['peopleGroup1']->getId();
        $this->client->request('GET', "/group/$id/new_support");

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('html', $content);
    }

    public function testNewSupportGroupPageIsUp()
    {
        $this->createLogin($this->data['userRoleUser']);

        $id = $this->data['peopleGroup2']->getId();
        $this->client->request('POST', "/group/$id/support/new", [
            'support' => ['service' => $this->data['service1']->getId()],
        ]);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Nouveau suivi');
    }

    public function testCreateNewSupportGroupIsSuccessful()
    {
        $user = $this->data['userRoleUser'];
        $this->createLogin($user);

        $id = $this->data['peopleGroup2']->getId();
        $this->client->request('POST', "/group/$id/support/new", [
            'support' => [
                'service' => $this->data['service1'],
                'device' => $this->data['device1']->getCode(),
                'referent' => $user,
            ],
        ]);

        $now = new \DateTime();
        $this->client->submitForm('send', [
            'support' => [
                'originRequest' => [
                    'organization' => $this->data['organization1'],
                    'organizationComment' => 'XXX',
                    'preAdmissionDate' => $now->format('Y-m-d'),
                    'resulPreAdmission' => 1,
                    'decisionDate' => $now->format('Y-m-d'),
                    'comment' => 'XXX',
                ],
                'service' => $this->data['service1'],
                'device' => $this->data['device1']->getCode(),
                'status' => SupportGroup::STATUS_IN_PROGRESS,
                'referent' => $user,
                'startDate' => $now->format('Y-m-d'),
                'agreement' => true,
            ],
        ]);

        // dump($this->client->getResponse()->getContent());
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorExists('.alert.alert-success');
    }

    public function testCreateNewSupportGroupAndCloneIsSuccessful()
    {
        $user = $this->data['userRoleUser'];
        $this->createLogin($user);

        $id = $this->data['peopleGroup2']->getId();
        $this->client->request('POST', "/group/$id/support/new", [
            'support' => ['service' => $this->data['service1']->getId()],
        ]);

        $this->client->submitForm('send', [
            'support' => [
                'service' => $this->data['service1'],
                'device' => $this->data['device1']->getCode(),
                'status' => SupportGroup::STATUS_IN_PROGRESS,
                'agreement' => true,
                'cloneSupport' => true,
            ],
        ]);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorExists('.alert.alert-success');
    }

    public function testPeopleHaveOtherSupportInProgress()
    {
        $user = $this->data['userRoleUser'];
        $this->createLogin($user);

        $id = $this->data['peopleGroup1']->getId();
        $this->client->request('POST', "/group/$id/support/new", [
            'support' => ['service' => $this->data['service1']->getId()],
        ]);

        $this->client->submitForm('send', [
            'support[service]' => $this->data['service1'],
            'support[device]' => $this->data['device1']->getCode(),
            'support[status]' => SupportGroup::STATUS_IN_PROGRESS,
            'support[agreement]' => true,
        ]);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('.alert.alert-danger', 'Attention, un suivi social est déjà en cours');
    }

    public function testEditSupportGroupIsSuccessful()
    {
        $this->createLogin($this->data['userRoleUser']);

        $id = $this->data['supportGroup1']->getId();
        $this->client->request('GET', "/support/$id/edit");

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Édition du suivi');

        $this->client->submitForm('send');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorExists('.alert.alert-success');
    }

    public function testEditCoefficientIsSuccessful()
    {
        $this->createLogin($this->data['userAdmin']);

        $id = $this->data['supportGroup2']->getId();
        $this->client->request('GET', "/support/$id/edit");

        $this->client->submitForm('send-coeff', [
            'support_coefficient[coefficient]' => 2,
        ]);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('.alert.alert-success', 'Le coefficient du suivi est mis à jour.');
    }

    public function testViewSupportGroupIsUp()
    {
        $this->data = $this->loadFixtureFiles([
            dirname(__DIR__).'/../DataFixturesTest/UserFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/ServiceFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/PersonFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/SupportFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/EvaluationFixturesTest.yaml',
        ]);

        $this->createLogin($this->data['userRoleUser']);

        $id = $this->data['supportGroupWithEval']->getId();
        $this->client->request('GET', "/support/$id/view");

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Suivi social');
    }

    public function testDeleteSupportIsFailed()
    {
        $this->createLogin($this->data['userRoleUser']);

        $id = $this->data['supportGroup1']->getId();
        $this->client->request('GET', "/support/$id/delete");

        $this->assertSame(Response::HTTP_FORBIDDEN, $this->client->getResponse()->getStatusCode());
    }

    public function testDeleteSupportIsSuccessful()
    {
        $this->createLogin($this->data['userAdmin']);

        $id = $this->data['supportGroup1']->getId();
        $this->client->request('GET', "/support/$id/delete");

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Groupe');
        $this->assertSelectorExists('.alert.alert-warning');
    }

    public function testAddPersonToSupportIsSuccessful()
    {
        $this->createLogin($this->data['userSuperAdmin']);

        $person = $this->data['person5'];
        $personId = $person->getId();
        $groupId = $this->data['peopleGroup1']->getId();
        $supportId = $this->data['supportGroup1']->getId();

        /** @var Crawler */
        $crawler = $this->client->request('GET', "/group/$groupId/search_person");

        $this->client->request('POST', "/group/$groupId/add_person/$personId", [
            'role_person' => [
                'role' => 1,
                'addPersonToSupport' => false,
                '_token' => $crawler->filter('#role_person__token')->attr('value'),
            ],
        ]);

        /** @var Crawler */
        $crawler = $this->client->request('GET', "/support/$supportId/edit");

        $this->client->submitForm('add-person', [
            'add_person_to_support[rolePerson]' => $crawler->filter('#add_person_to_support_rolePerson option')->last()->attr('value'),
        ]);

        $this->assertSelectorTextContains(
            '.alert.alert-success',
            $person->getFullname().' est ajouté'.Grammar::gender($person->getGender()).' au suivi en cours.'
        );
    }

    public function testRemoveSupportPersonWithoutTokenIsFailed()
    {
        $this->createLogin($this->data['userRoleUser']);

        $id = $this->data['supportGroup1']->getId();
        $supportPersId = $this->data['supportPerson2']->getId();
        $this->client->request('GET', "/support/$id/edit");
        $this->client->request('GET', "/supportGroup/$id/remove-$supportPersId/tokenId");

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('.alert.alert-danger', 'Une erreur');
    }

    public function testRemoveSupportPersonIsSuccessful()
    {
        $this->createLogin($this->data['userRoleUser']);

        $id = $this->data['supportGroup1']->getId();
        /** @var Crawler */
        $crawler = $this->client->request('GET', "/support/$id/edit");
        $url = $crawler->filter('button[data-action="remove"]')->last()->attr('data-url');
        $this->client->request('GET', $url);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('.alert.alert-warning', 'est retiré');
    }

    public function testCloneSupportIsFailed()
    {
        $this->createLogin($this->data['userSuperAdmin']);

        $id = $this->data['supportGroup2']->getId();
        $this->client->request('GET', "/support/$id/clone");

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('.alert.alert-warning', 'Aucun autre suivi n\'a été trouvé.');
    }

    public function testCloneSupportIsSuccessful()
    {
        $this->createLogin($this->data['userSuperAdmin']);

        $id = $this->data['supportGroup1']->getId();
        $this->client->request('GET', "/support/$id/clone");

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('.alert.alert-success', 'Les informations du précédent suivi ont été ajoutées');
    }

    public function testShowSupportsWithContributioIsUp()
    {
        $this->createLogin($this->data['userRoleUser']);

        $this->client->request('GET', '/supports/current_month');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Suivis en présence');
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
