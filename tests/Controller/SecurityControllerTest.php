<?php

namespace App\Tests\Controller;

use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class SecurityController extends WebTestCase
{
    use FixturesTrait;

    /** @var KernelBrowser */
    protected $client;

    protected function setUp()
    {
        $this->loadFixtureFiles([
            dirname(__DIR__) . "/DataFixturesTest/UserFixturesTest.yaml",
        ]);

        $this->client = static::createClient();
    }

    protected function generateURl($route)
    {
        return $this->client->getContainer()->get("router")->generate($route);
    }

    public function testLoginPage()
    {
        $this->client->request("GET", $this->generateURl("security_login"));

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testH1LoginPage()
    {
        $this->client->request("GET", $this->generateURl("security_login"));

        $this->assertSelectorTextContains("h1", "Merci de vous connecter");
        $this->assertSelectorNotExists(".alert-dismissible");
    }

    // public function testAuthHomePage()
    // {
    //     $this->client->request("GET", $this->generateURl("home"));

    //     $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    // }

    // public function testRedirectToLogin()
    // {
    //     $this->client->request("GET", $this->generateURl("home"));

    //     $this->assertResponseRedirects($this->generateURl("security_login"));
    // }

    public function testFailLogin()
    {
        $crawler = $this->client->request("GET", $this->generateURl("security_login"));

        $form = $crawler->selectButton("Se connecter")->form([
            "_username" => "badUsername",
            "_password" => "wrongPassword"
        ]);

        $this->client->submit($form);

        // $this->assertResponseRedirects($this->generateURl("security_login"));
        $this->client->followRedirect();

        // $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorExists(".alert.alert-danger");
    }

    public function testSuccessLogin()
    {
        $this->client = static::createClient();

        $csrfToken = $this->client->getContainer()->get("security.csrf.token_manager")->getToken("authenticate");

        $this->client->request("POST", $this->generateURl("security_login"), [
            "_username" => "r.madelaine",
            "_password" => "Test123*",
            "_csrf_token" => $csrfToken
        ]);

        $this->client->followRedirect();
        $this->client->followRedirect();

        $this->assertSelectorExists(".alert.alert-success");
    }
}
