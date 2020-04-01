<?php

namespace App\Tests\Controller;

use App\Tests\Controller\ControllerTestTrait;
use Symfony\Component\HttpFoundation\Response;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityController extends WebTestCase
{
    use FixturesTrait;
    use ControllerTestTrait;


    /** @var KernelBrowser */
    protected $client;

    protected function setUp()
    {
        $this->loadFixtureFiles([
            dirname(__DIR__) . "/DataFixturesTest/UserFixturesTest.yaml",
        ]);

        $this->client = static::createClient();
    }

    public function testLoginPage()
    {
        $this->client->request("GET", $this->generateUri("security_login"));

        static::assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testH1LoginPage()
    {
        $this->client->request("GET", $this->generateUri("security_login"));

        static::assertSelectorTextContains("h1", "Merci de vous connecter");
        static::assertSelectorNotExists(".alert-dismissible");
    }

    // public function testAuthHomePage()
    // {
    //     $this->client->request("GET", $this->generateUri("home"));

    //     static::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    // }

    // public function testRedirectToLogin()
    // {
    //     $this->client->request("GET", $this->generateUri("home"));

    //     static::assertResponseRedirects($this->generateUri("security_login"));
    // }

    public function testFailLogin()
    {
        $crawler = $this->client->request("GET", $this->generateUri("security_login"));

        $form = $crawler->selectButton("Se connecter")->form([
            "_username" => "badUsername",
            "_password" => "wrongPassword"
        ]);

        $this->client->submit($form);

        // $this->assertResponseRedirects($this->generateUri("security_login"));
        $this->client->followRedirect();

        // static::assertResponseStatusCodeSame(Response::HTTP_OK);
        static::assertSelectorExists(".alert.alert-danger");
    }

    public function testSuccessLogin()
    {
        $csrfToken = $this->client->getContainer()->get("security.csrf.token_manager")->getToken("authenticate");

        $this->client->request("POST", $this->generateUri("security_login"), [
            "_username" => "r.madelaine",
            "_password" => "Test123*",
            "_csrf_token" => $csrfToken
        ]);

        $this->client->followRedirects(true);
        $this->client->followRedirect();

        static::assertSelectorExists(".alert.alert-success");
    }
}
