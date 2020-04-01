<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Symfony\Component\DomCrawler\Crawler;
use App\Tests\Controller\ControllerTestTrait;
use Symfony\Component\HttpFoundation\Response;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserControllerTest extends WebTestCase
{
    use FixturesTrait;
    use ControllerTestTrait;

    /** @var KernelBrowser */
    protected $client;

    /** @var array */
    protected $dataFixtures;

    /** @var User */
    protected $user;

    protected function setUp()
    {
        $this->dataFixtures = $this->loadFixtureFiles([
            dirname(__DIR__) . "/DataFixturesTest/UserFixturesTest.yaml",
        ]);

        $this->createLoggedUser($this->dataFixtures);

        $this->user = $this->dataFixtures["userRoleUser"];
    }

    public function testListUsersPageIsUp()
    {
        /** @var Crawler */
        $crawler = $this->client->request("GET", $this->generateUri("users"));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains("h1", "Utilisateurs");
    }

    public function testAdminListUsersIsUp()
    {
        $this->client->request("GET", $this->generateUri("admin_users"));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains("h1", "Administration des utilisateurs");
    }

    public function testUsernameExistsIsTrue()
    {
        $this->client->request("GET", $this->generateUri("username_exists", [
            "value" => "r.madelaine"
        ]));

        $result = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertTrue($result["response"]);
    }

    public function testUsernameExistsIsFalse()
    {
        $this->client->request("GET", $this->generateUri("username_exists", [
            "value" => "xxx"
        ]));

        $result = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertNotTrue($result["response"]);
    }
}
