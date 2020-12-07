<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Tests\AppTestTrait;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class SecurityController extends WebTestCase
{
    use FixturesTrait;
    use AppTestTrait;

    /** @var KernelBrowser */
    protected $client;

    /** @var array */
    protected $dataFixtures;

    /** @var User */
    protected $user;

    protected function setUp()
    {
        $this->dataFixtures = $this->loadFixtureFiles([
            dirname(__DIR__).'/DataFixturesTest/UserFixturesTest.yaml',
        ]);

        $this->user = $this->dataFixtures['userSuperAdmin'];
    }

    public function testLoginPage()
    {
        $this->client = static::createClient();
        $this->client->request('GET', $this->generateUri('security_login'));

        static::assertResponseStatusCodeSame(Response::HTTP_OK);
        static::assertSelectorTextContains('h1', 'Merci de vous connecter');
        static::assertSelectorNotExists('.alert-dismissible');
    }

    public function testRegistrationIsUp()
    {
        $this->createLogin($this->dataFixtures['userRoleAdmin']);
        $this->client->request('GET', $this->generateUri('security_registration'));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Création d\'un compte utilisateur');
    }

    public function testSendNewEmailToUser()
    {
        $this->createLogin($this->dataFixtures['userRoleAdmin']);

        $user = $this->dataFixtures['userRoleUser'];

        $this->client->request('GET', $this->generateUri('security_user_send_new_email', [
            'id' => $user->getId(),
        ]));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', $user->getFullname());
    }

    // public function testAfterLoginIsUp()
    // {
    //     $this->createLogin($this->dataFixtures['userRoleUser']);
    //     $this->client->request('GET', $this->generateUri('security_after_login'));
    //     // $this->client->followRedirect();
    //     $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    //     $this->assertSelectorTextContains('h1', 'Tableau de bord');
    // }

    public function testInitPasswordIsUp()
    {
        $this->createLogin($this->dataFixtures['userRoleUser']);
        $this->client->request('GET', $this->generateUri('security_init_password'));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Personnalisation du mot de passe');
    }

    public function testShowCurrentUserIsUp()
    {
        $user = $this->dataFixtures['userRoleUser'];

        $this->createLogin($user);
        $this->client->request('GET', $this->generateUri('my_profile'));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorExists('h1', $user->getFullname());
    }

    public function testEditUserIsUp()
    {
        $user = $this->dataFixtures['userRoleAdmin'];
        $this->createLogin($user);

        $this->client->request('GET', $this->generateUri('security_user', [
            'id' => $user->getId(),
        ]));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', $user->getFullname());
    }

    // public function testForgotPasswordIsUp()
    // {
    //     $this->client->request('GET', $this->generateUri('security_forgot_password'));

    //     $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    //     $this->assertSelectorTextContains('h1', 'Mot de passe oublié');
    // }

    public function testReinitPasswordIsUp()
    {
        $this->createLogin($this->dataFixtures['userRoleUser']);
        $this->client->request('GET', $this->generateUri('security_reinit_password'));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Réinitialisation du mot de passe');
    }

    // public function testAuthHomePage()
    // {
    //     $this->client->request('GET', $this->generateUri('home'));

    //     static::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    // }

    // public function testRedirectToLogin()
    // {
    //     $this->client->request('GET', $this->generateUri('home'));

    //     static::assertResponseRedirects($this->generateUri('security_login'));
    // }

    public function testFailLogin()
    {
        $this->client = static::createClient();
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->generateUri('security_login'));

        $form = $crawler->selectButton('send')->form([
            '_username' => 'badUsername',
            '_password' => 'wrongPassword',
        ]);

        $this->client->submit($form);

        static::assertSelectorExists('.alert.alert-danger');
    }

    public function testSuccessLogin()
    {
        $this->client = static::createClient();
        $this->client->followRedirects();

        $csrfToken = $this->client->getContainer()->get('security.csrf.token_manager')->getToken('authenticate');

        $this->client->request('POST', $this->generateUri('security_login'), [
            '_username' => 'r.super_admin',
            '_password' => 'Test123*',
            '_csrf_token' => $csrfToken,
        ]);

        static::assertSelectorExists('.alert.alert-success');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->client = null;
        $this->dataFixtures = null;
    }
}
