<?php

namespace App\Tests;

use App\Entity\Organization\User;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Panther\Client;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

trait AppTestTrait
{
    // /** @var KernelBrowser */
    // protected $client;

    /**
     * Crée une connexion.
     */
    protected function createLogin(User $user, bool $followRedirects = true): void
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
            '_username' => $user->getUsername(),
            '_password' => $user->getPlainPassword(),
            '_csrf_token' => $this->client->getContainer()->get('security.csrf.token_manager')->getToken('authenticate'),
        ]);
    }

    protected function createPantherLogin($followRedirects = true)
    {
        $this->client = Client::createChromeClient(__DIR__.'/../drivers/chromedriver');
        // $this->client = Client::createFirefoxClient(__DIR__.'/../drivers/geckodriver');

        $followRedirects ? $this->client->followRedirects() : null;

        $crawler = $this->client->request('GET', '/');

        $this->debug('try to login');

        $form = $crawler->selectButton('send')->form([
            '_username' => 'r.admin',
            '_password' => 'Test123*',
        ]);

        $this->client->submit($form);
    }

    /**
     * Generate an URI.
     */
    protected function generateUri(string $route, array $parameters = []): string
    {
        return $this->client->getContainer()->get('router')->generate($route, $parameters);
    }

    /**
     * Generate an URI.
     */
    protected function generatePantherUri(string $route, array $parameters = []): string
    {
        return self::$container->get('router')->generate($route, $parameters);
    }

    public function debug(string $message)
    {
        // $message = "test : \e[20mXXXX \e[36mCyan \e[35mViolet \e[33mYellow \e[34mBlue \e[32mGreen \e[31mRed \e[37mWhite \e[0m \n";
        file_put_contents('php://stdout', "\e[34mtest : \e[36m".$message."\e[0m \n");
    }
}
