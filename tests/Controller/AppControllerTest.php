<?php

namespace App\Tests\Controller;

use App\Tests\Controller\ControllerTestTrait;
use Symfony\Component\HttpFoundation\Response;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AppControllerTest extends WebTestCase
{
    use FixturesTrait;
    use ControllerTestTrait;

    protected function setUp()
    {
        $dataFixtures = $this->loadFixtureFiles([
            dirname(__DIR__) . "/DataFixturesTest/UserFixturesTest.yaml",
        ]);

        $this->createLoggedUser($dataFixtures);
    }

    public function testHomepageIsUp()
    {
        $this->client->request("GET", "/");
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }

    public function testAccessHomePage()
    {
        $csrfToken = $this->client->getContainer()->get("security.csrf.token_manager")->getToken("authenticate");

        $this->client->request("POST", $this->generateUri("security_login"), [
            "_username" => "r.madelaine",
            "_password" => "Test123*",
            "_csrf_token" => $csrfToken
        ]);

        $this->client->followRedirects(true);
        $this->client->followRedirect();

        $this->assertSelectorExists(".alert.alert-success");
    }
}
