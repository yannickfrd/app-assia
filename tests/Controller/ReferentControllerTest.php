<?php

namespace App\Tests\Controller;

use App\Entity\Referent;
use App\Tests\AppTestTrait;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class ReferentControllerTest extends WebTestCase
{
    use FixturesTrait;
    use AppTestTrait;

    /** @var KernelBrowser */
    protected $client;

    /** @var array */
    protected $dataFixtures;

    /** @var Referent */
    protected $referent;

    protected function setUp()
    {
        $this->dataFixtures = $this->loadFixtureFiles([
            dirname(__DIR__).'/DataFixturesTest/ReferentFixturesTest.yaml',
        ]);

        $this->createLogin($this->dataFixtures['userSuperAdmin']);

        $this->referent = $this->dataFixtures['referent1'];
    }

    public function testNewReferentIsUp()
    {
        $this->client->request('GET', $this->generateUri('referent_new', [
            'id' => $this->dataFixtures['groupPeople']->getId(),
        ]));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Nouveau service social référent');
    }

    public function testCreateNewReferentIsSuccessful()
    {
        /** @var Crawler */
        $crawler = $this->client->request('GET', $this->generateUri('referent_new', [
            'id' => $this->dataFixtures['groupPeople']->getId(),
        ]));

        $form = $crawler->selectButton('send')->form([
            'referent[name]' => 'Référent test',
            'referent[type]' => 1,
            'referent[socialWorker]' => 'XXXX',
            'referent[socialWorker2]' => 'XXXX',
        ]);

        $this->client->submit($form);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorExists('.alert.alert-success');
    }

    public function testEditReferentIsUp()
    {
        $this->client->request('GET', $this->generateUri('referent_edit', [
            'id' => $this->referent->getId(),
        ]));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', $this->referent->getName());
    }

    public function testEditReferentIsSucessful()
    {
        /** @var Crawler */
        $crawler = $this->client->request('GET', $this->generateUri('referent_edit', [
            'id' => $this->referent->getId(),
        ]));

        $form = $crawler->selectButton('send')->form([]);

        $this->client->submit($form);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', $this->referent->getName());
    }

    public function testDeleteReferent()
    {
        $this->client->request('GET', $this->generateUri('referent_delete', [
            'id' => $this->referent->getId(),
        ]));
        // $this->client->followRedirect();
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Group');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->client = null;
        $this->dataFixtures = null;
    }
}
