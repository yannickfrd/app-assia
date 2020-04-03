<?php

namespace App\Tests\Controller;

use App\Entity\Organization;
use Symfony\Component\DomCrawler\Crawler;
use App\Tests\AppTestTrait;
use Symfony\Component\HttpFoundation\Response;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class OrganizationControllerTest extends WebTestCase
{
    use FixturesTrait;
    use AppTestTrait;

    /** @var KernelBrowser */
    protected $client;

    /** @var array */
    protected $dataFixtures;

    /** @var Organization */
    protected $organization;

    protected function setUp()
    {
        $this->dataFixtures = $this->loadFixtureFiles([
            dirname(__DIR__) . "/DataFixturesTest/UserFixturesTest.yaml",
            dirname(__DIR__) . "/DataFixturesTest/OrganizationFixturesTest.yaml"
        ]);

        $this->createLogin($this->dataFixtures["userSuperAdmin"]);

        $this->organization = $this->dataFixtures["organization1"];
    }

    public function testListOrganizationsIsUp()
    {
        $this->client->request("GET", $this->generateUri("admin_organizations"));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains("h1", "Organismes prescripteurs");
    }

    public function testNewOrganizationIsUp()
    {
        $this->client->request("GET", $this->generateUri("admin_organization_new"));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains("h1", "Nouvel organisme");
    }

    public function testEditOrganizationisUp()
    {
        $this->client->request("GET", $this->generateUri("admin_organization_edit", [
            "id" => $this->organization->getId()
        ]));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains("h1", $this->organization->getName());
    }

    protected function tearDown()
    {
        parent::tearDown();
        $this->client = null;
        $this->dataFixtures = null;
    }
}
