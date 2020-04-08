<?php

namespace App\Tests;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

trait AppTestTrait
{
    // /** @var KernelBrowser */
    // protected $client;

    /**
     * Crée une connexion.
     *
     * @param bool $followRedirects
     *
     * @return void
     */
    protected function createLogin(User $user, $followRedirects = true)
    {
        /* @var KernelBrowser */
        $this->client = static::createClient();

        $followRedirects ? $this->client->followRedirects() : null;

        $session = $this->client->getContainer()->get('session');
        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        $session->set('security_main', serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);

        $this->client->request('POST', $this->generateUri('security_login'), [
            '_username' => 'r.madelaine',
            '_password' => 'Test123*',
            '_csrf_token' => $this->client->getContainer()->get('security.csrf.token_manager')->getToken('authenticate'),
        ]);
    }

    protected function createPantherLogin($followRedirects = true)
    {
        $this->client = static::createPantherClient();

        $followRedirects ? $this->client->followRedirects() : null;

        $crawler = $this->client->request('GET', '/');

        dump('Test : try to login');

        $form = $crawler->selectButton('send')->form([
            '_username' => 'r.madelaine',
            '_password' => 'Test123*',
        ]);

        $this->client->submit($form);
    }

    /**
     * Generate an URI.
     *
     * @return string $uri
     */
    protected function generateUri(string $route, array $parameters = [])
    {
        return $this->client->getContainer()->get('router')->generate($route, $parameters);
    }

    /**
     * Generate an URI.
     *
     * @return string $uri
     */
    protected function generatePantherUri(string $route, array $parameters = [])
    {
        return self::$container->get('router')->generate($route, $parameters);
    }
}
