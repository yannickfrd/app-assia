<?php

namespace App\Tests;

use App\Entity\Organization\User;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

trait AppTestTrait
{
    /**
     * Crée une connexion.
     */
    protected function createLogin(User $user, bool $followRedirects = true): void
    {
        /* @var KernelBrowser */
        // $this->client = static::createClient();

        $followRedirects ? $this->client->followRedirects() : null;

        $session = $this->client->getContainer()->get('session');
        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        $session->set('security_main', serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);

        $this->client->request('POST', '/login', [
            'username' => $user->getUsername(),
            'password' => $user->getPlainPassword(),
            '_csrf_token' => $this->client->getContainer()->get('security.csrf.token_manager')->getToken('authenticate'),
        ]);
    }

    /**
     * Generate an URI.
     */
    protected function generateUri(string $route, array $parameters = []): string
    {
        return $this->client->getContainer()->get('router')->generate($route, $parameters);
    }
}
