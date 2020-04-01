<?php

namespace App\Tests\Controller;

use Symfony\Component\BrowserKit\Cookie;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

trait ControllerTestTrait
{
    /** @var KernelBrowser */
    protected $client;

    protected function createLoggedUser($dataFixtures)
    {
        $this->client = static::createClient();

        /** @var User */
        $userSuperAdmin = $dataFixtures["userSuperAdmin"];

        $session  = $this->client->getContainer()->get("session");
        $token = new UsernamePasswordToken($userSuperAdmin, null, "main", $userSuperAdmin->getRoles());
        $session->set("security_main", serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);

        $this->client->request("POST", $this->generateUri("security_login"), [
            "_username" => "r.madelaine",
            "_password" => "Test123*",
            "_csrf_token" => $this->client->getContainer()->get("security.csrf.token_manager")->getToken("authenticate")
        ]);
    }

    /**
     * Generate an URI
     *
     * @param string $route
     * @param array $parameters
     * @return string $uri
     */
    protected function generateUri(string $route, array $parameters = [])
    {
        return $this->client->getContainer()->get("router")->generate($route, $parameters);
    }
}
