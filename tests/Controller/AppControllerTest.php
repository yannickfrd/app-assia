<?php

namespace App\Tests\Controller;

use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Response;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class AppControllerTest extends WebTestCase
{
    use FixturesTrait;

    /** @var KernelBrowser */
    protected $client;

    protected function setUp()
    {
        $this->client = static::createClient();

        $dataFixtures = $this->loadFixtureFiles([
            dirname(__DIR__) . "/DataFixtures/UserFixturesTest.yaml",
        ]);

        /** @var User */
        $userAdmin = $dataFixtures["userAdmin"];

        $session  = $this->client->getContainer()->get("session");
        $token = new UsernamePasswordToken($userAdmin, null, "main", $userAdmin->getRoles());
        $session->set("security_main", serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);
    }

    protected function generateURl($route)
    {
        return $this->client->getContainer()->get("router")->generate($route);
    }

    public function testAccessHomePage()
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
