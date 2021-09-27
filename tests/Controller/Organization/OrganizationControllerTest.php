<?php

namespace App\Tests\Controller;

use App\Entity\Organization\Organization;
use App\Tests\AppTestTrait;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class OrganizationControllerTest extends WebTestCase
{
    use FixturesTrait;
    use AppTestTrait;

    /** @var KernelBrowser */
    protected $client;

    /** @var array */
    protected $data;

    /** @var Organization */
    protected $organization;

    protected function setUp(): void
    {
        $this->data = $this->loadFixtureFiles([
            dirname(__DIR__).'/../DataFixturesTest/UserFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/OrganizationFixturesTest.yaml',
        ]);

        $this->createLogin($this->data['userAdmin']);

        $this->organization = $this->data['organization1'];
    }

    public function testListOrganizationsIsUp()
    {
        $this->client->request('GET', '/organizations');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Organismes prescripteurs');
    }

    public function testCreateNewOrganizationIsSuccessful()
    {
        $this->client->request('GET', '/admin/organization/new');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Nouvel organisme');

        $this->client->submitForm('send', [
            'organization[name]' => 'Organisme test',
            'organization[comment]' => 'XXX',
        ]);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorExists('.alert.alert-success');
    }

    public function testEditOrganizationIsSuccessful()
    {
        $id = $this->organization->getId();
        $this->client->request('GET', "/admin/organization/ $id");

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', $this->organization->getName());

        $this->client->submitForm('send');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorExists('.alert.alert-success');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->client = null;
        $this->data = null;
    }
}
