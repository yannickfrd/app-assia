<?php

namespace App\Tests\Controller;

use App\Entity\Support\SupportGroup;
use App\Tests\AppTestTrait;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SupportControllerTest extends WebTestCase
{
    use FixturesTrait;
    use AppTestTrait;

    /** @var KernelBrowser */
    protected $client;

    /** @var array */
    protected $dataFixtures;

    /** @var SupportGroup */
    protected $supportGroup;

    protected function setUp()
    {
        $this->dataFixtures = $this->loadFixtureFiles([
            dirname(__DIR__).'/../DataFixturesTest/SupportFixturesTest.yaml',
        ]);

        $this->createLogin($this->dataFixtures['userRoleUser']);

        $this->supportGroup = $this->dataFixtures['supportGroup1'];
    }

    public function testviewListSupportsIsUp()
    {
        $this->client->request('GET', $this->generateUri('supports'));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Suivis');
    }

    public function testSearchSupportsIsSuccessful()
    {
        /** @var Crawler */
        $crawler = $this->client->request('GET', $this->generateUri('supports'));

        $form = $crawler->selectButton('search')->form([
            'fullname' => 'John Doe',
            // 'familyTypologies' => [1],
            'date[start]' => '2018-01-01',
            'date[end]' => (new \DateTime())->format('Y-m-d'),
        ]);

        $this->client->submit($form);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Suivis');
    }

    public function testExportSupports()
    {
        /** @var Crawler */
        $crawler = $this->client->request('GET', $this->generateUri('supports'));

        $form = $crawler->selectButton('export')->form([]);

        $this->client->submit($form);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }

    public function testNewSupportGroupIsUp()
    {
        $this->client->request(
            'POST',
            $this->generateUri('support_new', [
                'id' => $this->dataFixtures['peopleGroup']->getId(),
            ]),
            [
                'support' => ['service' => 1],
            ]);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Nouveau suivi');
    }

    public function testCreateNewSupportGroupIsSuccessful()
    {
        /** @var Crawler */
        $crawler = $this->client->request(
            'POST',
            $this->generateUri('support_new', [
                'id' => ($this->dataFixtures['peopleGroup'])->getId(),
            ]),
            [
                'support' => ['service' => 1],
            ],
        );

        $now = new \DateTime();
        $faker = \Faker\Factory::create('fr_FR');

        $form = $crawler->selectButton('send')->form([
            'support[originRequest][organization]' => 1,
            'support[originRequest][organizationComment]' => $faker->sentence(mt_rand(3, 6), true),
            'support[originRequest][preAdmissionDate]' => $now->format('Y-m-d'),
            'support[originRequest][resulPreAdmission]' => 1,
            'support[originRequest][decisionDate]' => $now->format('Y-m-d'),
            'support[originRequest][comment]' => $faker->paragraphs(6, true),
            'support[service]' => 1,
            'support[device]' => 1,
            'support[status]' => 2,
            'support[referent]' => 1,
            'support[startDate]' => $now->format('Y-m-d'),
            'support[agreement]' => true,
        ]);

        $this->client->submit($form);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        // $this->assertSelectorExists('.alert.alert-success');
    }

    public function testEditSupportGroupIsUp()
    {
        $this->client->request('GET', $this->generateUri('support_edit', [
            'id' => ($this->dataFixtures['supportGroup1'])->getId(),
        ]));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Édition du suivi');
    }

    public function testEditSupportGroupIsSuccessful()
    {
        /** @var Crawler */
        $crawler = $this->client->request('GET', $this->generateUri('support_edit', [
            'id' => ($this->dataFixtures['supportGroup1'])->getId(),
        ]));

        $form = $crawler->selectButton('send')->form([]);

        $this->client->submit($form);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorExists('.alert.alert-success');
    }

    public function testViewSupportGroupIsUp()
    {
        $this->client->request('GET', $this->generateUri('support_view', [
            'id' => ($this->dataFixtures['supportGroup1'])->getId(),
        ]));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Suivi social');
    }

    public function testSuccessDeleteSupport()
    {
        $this->client->request('GET', $this->generateUri('support_delete', [
            'id' => ($this->dataFixtures['supportGroup1'])->getId(),
        ]));
        // $this->client->followRedirect();
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Groupe');
        $this->assertSelectorExists('.alert.alert-warning');
    }

    // public function testRemoveSupportPerson()
    // {
    //     $supportPerson = ($this->dataFixtures['supportPerson1']);
    //     $csrfToken = $this->client->getContainer()->get('security.csrf.token_manager')->getToken('remove' . $supportPerson->getId());

    //     $this->client->request('GET', $this->generateUri('remove_support_pers', [
    //         'id' => ($this->dataFixtures['supportGroup1'])->getId(),
    //         'support_pers_id' => $supportPerson->getid(),
    //         '_token' => $csrfToken
    //     ]));

    //     $result = json_decode($this->client->getResponse()->getContent(), true);

    //     $this->assertSame(200, $result['code']);
    // }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->client = null;
        $this->dataFixtures = null;
    }
}
