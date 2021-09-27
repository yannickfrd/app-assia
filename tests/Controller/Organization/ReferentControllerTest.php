<?php

namespace App\Tests\Controller;

use App\Entity\Organization\Referent;
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
    protected $data;

    /** @var Referent */
    protected $referent;

    protected function setUp(): void
    {
    }

    protected function getFixtureFiles()
    {
        return [
            dirname(__DIR__).'/../DataFixturesTest/UserFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/ServiceFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/PersonFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/ReferentFixturesTest.yaml',
        ];
    }

    public function testCreateReferentByPeopleGroupIsSuccessful()
    {
        $data = $this->loadFixtureFiles($this->getFixtureFiles());

        $this->createLogin($data['userRoleUser']);

        $id = $data['peopleGroup1']->getId();
        $this->client->request('GET', "/group/$id/referent/new");

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Nouveau service social référent');

        $this->client->submitForm('send', [
            'referent' => [
                'name' => 'Référent test',
                'type' => 1,
                'socialWorker' => 'XXXX',
                'socialWorker2' => 'XXXX',
            ],
        ]);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorExists('.alert.alert-success');
    }

    public function testEditReferentIsSuccessful()
    {
        $data = $this->loadFixtureFiles($this->getFixtureFiles());

        $this->createLogin($data['userRoleUser']);

        $id = $data['referent1']->getId();
        $this->client->request('GET', "/referent/$id/edit");

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', $data['referent1']->getName());

        $this->client->submitForm('send', [
            'referent[name]' => 'Référent test edit',
            'referent[type]' => 2,
        ]);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Référent test edit');
    }

    public function testDeleteReferentIsSuccessful()
    {
        $data = $this->loadFixtureFiles($this->getFixtureFiles());

        $this->createLogin($data['userRoleUser']);

        $id = $data['referent1']->getId();
        $this->client->request('GET', "/referent/$id/delete");

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Group');
    }

    public function testCreateReferentBySupportIsSuccessful()
    {
        $data = $this->loadFixtureFiles(array_merge($this->getFixtureFiles(), [
            dirname(__DIR__).'/../DataFixturesTest/SupportFixturesTest.yaml',
        ]));

        $this->createLogin($data['userRoleUser']);

        $id = $data['supportGroup1']->getId();
        $this->client->request('GET', "/suppport/$id/referent/new");

        $this->client->submitForm('send', [
            'referent[name]' => 'Référent test',
            'referent[type]' => 1,
        ]);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorExists('.alert.alert-success');

        $this->client->clickLink('Supprimer');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('.alert.alert-warning', 'Le service social Référent test est supprimé.');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->client = null;
        $data = null;
    }
}
