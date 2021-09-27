<?php

namespace App\Tests\Controller\Admin;

use App\Entity\Organization\User;
use App\Tests\AppTestTrait;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class SecurityControllerTest extends WebTestCase
{
    use FixturesTrait;
    use AppTestTrait;

    /** @var KernelBrowser */
    protected $client;

    /** @var array */
    protected $data;

    /** @var User */
    protected $user;

    protected function setUp(): void
    {
        $this->data = $this->loadFixtureFiles([
            dirname(__DIR__).'/../DataFixturesTest/UserFixturesTest.yaml',
        ]);
    }

    public function testLoginIsSuccessful()
    {
        $this->client = static::createClient();
        $this->client->followRedirects();

        $this->client->request('GET', '/login');

        static::assertResponseStatusCodeSame(Response::HTTP_OK);
        static::assertSelectorTextContains('h1', 'Merci de vous connecter');

        $csrfToken = $this->client->getContainer()->get('security.csrf.token_manager')->getToken('authenticate');

        $this->client->request('POST', '/login', [
            '_username' => 'r.user',
            '_password' => 'Test123*',
            '_csrf_token' => $csrfToken,
        ]);

        static::assertSelectorExists('.alert.alert-success');

        // Test redirect to home page
        $this->client->request('GET', '/login');
        static::assertSelectorTextContains('h1', 'Tableau de bord');

        // Test redirect to home page
        $this->client->request('GET', '/login/forgot_password');
        static::assertSelectorTextContains('h1', 'Tableau de bord');

        // Test redirect to logout
        $this->client->request('GET', '/login/create_password/token');
        $this->assertSelectorTextContains('h1', 'Merci de vous connecter');

        // Test logout
        $this->client->request('GET', '/deconnexion');
        static::assertSelectorTextContains('h1', 'Merci de vous connecter');
    }

    public function testRegistrationIsFailed()
    {
        $this->createLogin($this->data['userAdmin']);

        /** @var Crawler */
        $crawler = $this->client->request('GET', '/admin/registration');
        $csrfToken = $crawler->filter('#user__token')->attr('value');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Création d\'un compte utilisateur');

        $this->client->request('POST', '/admin/registration', [
            'user' => [
                'firstname' => 'John',
                'lastname' => 'DOE',
                'email' => 'j.doe@mail.fr',
                'status' => User::STATUS_SOCIAL_WORKER,
                'roles' => ['ROLE_USER'],
                'username' => 'j.doe',
                '_token' => $csrfToken,
            ],
        ]);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('.alert.alert-danger', "Veuillez rattacher l'utilisateur au minimum à un service.");
    }

    public function testRegistrationIsSuccessful()
    {
        $this->createLogin($this->data['userAdmin']);

        $this->createNewUser();

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('.alert.alert-success', 'Le compte de John est créé');
    }

    public function testSendNewEmailToUser()
    {
        $this->createLogin($this->data['userAdmin']);

        $user = $this->data['userRoleUser'];

        $id = $user->getId();
        $this->client->request('GET', "/admin/user/$id/send_new_email");

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', $user->getFullname());
    }

    public function testInitPasswordIsSuccessful()
    {
        $this->createLogin($this->data['userRoleUser']);
        $this->client->request('GET', '/login/init_password');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Personnalisation du mot de passe');

        $this->client->submitForm('send', [
            'init_password' => [
                'password' => 'Test123*',
                'confirmPassword' => 'Test123*',
            ],
        ]);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorExists('.alert.alert-success', 'Votre mot de passe est mis à jour !');
    }

    public function testEditCurrentUserIsSuccessful()
    {
        $user = $this->data['userRoleUser'];

        $this->createLogin($user);
        $this->client->request('GET', '/my_profile');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorExists('h1', $user->getFullname());

        $this->client->submitForm('send', [
            'user' => [
                'email' => 'j.doe@mail.fr',
                'phone1' => '0102030405',
                'phone2' => '0602030405',
            ],
        ]);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorExists('.alert.alert-success', 'Les modifications sont enregistrées.');

        $this->client->submitForm('send2', [
            'change_password' => [
                'oldPassword' => 'Test123',
                'newPassword' => 'Test123*',
                'confirmNewPassword' => 'Test123',
            ],
        ]);
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorExists('.alert.alert-danger', 'Le mot de passe ou la confirmation sont invalides.');

        $this->client->submitForm('send2', [
            'change_password' => [
                'oldPassword' => 'Test123*',
                'newPassword' => 'Test123*',
                'confirmNewPassword' => 'Test123*',
            ],
        ]);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorExists('.alert.alert-success', 'Votre mot de passe est mis à jour !');
    }

    public function testEditUserIsSuccessful()
    {
        $user = $this->data['userAdmin'];
        $this->createLogin($user);

        $id = $user->getId();
        $this->client->request('GET', "/admin/user/$id");

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', $user->getFullname());

        $this->client->submitForm('send', [
            'user' => [
                'email' => 'j.doe@mail.fr',
                'phone1' => '0102030405',
            ],
        ]);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorExists('.alert.alert-success', 'Les modifications sont enregistrées.');
    }

    public function testDisableUserIsSuccessful()
    {
        $user = $this->data['userAdmin'];
        $this->createLogin($user);

        $id = $user->getId();
        $this->client->request('GET', "/admin/user/$id/disable");

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorExists('.alert.alert-danger', 'Vous ne pouvez pas vous-même désactiver votre compte utilisateur.');

        $id = $this->data['userRoleUser']->getId();
        $this->client->request('GET', "/admin/user/$id/disable");

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorExists('.alert.alert-warning', 'Ce compte utilisateur est désactivé.');

        $this->client->request('GET', "/admin/user/$id/disable");

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorExists('.alert.alert-success', 'Ce compte utilisateur est ré-activé.');
    }

    public function testForgotPasswordIsSuccessful()
    {
        /** @var User */
        $user = $this->data['userRoleUser'];

        $this->client = static::createClient();
        $this->client->followRedirects();
        $this->client->request('GET', '/login/forgot_password');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Mot de passe oublié');

        // Fail
        $this->client->submitForm('send', [
            'forgot_password' => [
                'username' => 'bad_username',
                'email' => 'bad_username@mail.fr',
            ],
        ]);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('.alert.alert-danger', 'Le login ou l\'adresse email sont incorrects.');

        // Success
        $this->client->submitForm('send', [
            'forgot_password' => [
                'username' => $user->getUsername(),
                'email' => $user->getEmail(),
            ],
        ]);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('.alert.alert-success', 'Un mail vous a été envoyé');
    }

    public function testReinitPasswordIsSuccessful()
    {
        /** @var User */
        $user = $this->data['userRoleUser'];

        $this->client = static::createClient();
        $this->client->followRedirects();

        $this->client->request('GET', '/login/forgot_password');

        $this->client->submitForm('send', [
            'forgot_password' => [
                'username' => $user->getUsername(),
                'email' => $user->getEmail(),
            ],
        ]);

        $userRepo = $this->client->getContainer()->get('doctrine.orm.entity_manager')->getRepository(User::class);
        $user = $userRepo->findOneBy(['username' => $user->getUsername()]);

        // Fail with bad token
        $this->client->request('GET', '/login/reinit_password/token');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Réinitialisation du mot de passe');

        $this->client->submitForm('send', [
            'reinit_password' => [
                'username' => $user->getUsername(),
                'email' => $user->getEmail(),
                'password' => 'Test123*',
                'confirmPassword' => 'Test123*',
            ],
        ]);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('.alert.alert-danger', 'Le login ou l\'adresse email sont incorrects.');

        // Success with good token
        $this->client->request('GET', '/login/reinit_password/'.$user->getToken());

        $this->client->submitForm('send', [
            'reinit_password' => [
                'username' => $user->getUsername(),
                'email' => $user->getEmail(),
                'password' => 'Test123*',
                'confirmPassword' => 'Test123*',
            ],
        ]);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('.alert.alert-success', 'Votre mot de passe est réinitialisé !');
    }

    public function testCreatePasswordIsSuccessful()
    {
        $this->createLogin($this->data['userAdmin']);

        $this->createNewUser();
        $this->client->request('GET', '/deconnexion');

        $userRepo = $this->client->getContainer()->get('doctrine.orm.entity_manager')->getRepository(User::class);
        /** @var User */
        $user = $userRepo->findOneBy(['username' => 'j.doe']);

        // Fail with invalid token
        $this->client->request('GET', '/login/create_password/token');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('.alert.alert-danger', 'Le lien est expiré ou invalide.');

        // Fail with valid token
        $this->client->request('GET', '/login/create_password/'.$user->getToken());

        $this->client->submitForm('send', [
            'reinit_password' => [
                'username' => 'bad_login',
                'email' => $user->getEmail(),
                'password' => 'Test123*',
                'confirmPassword' => 'Test123*',
            ],
        ]);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('.alert.alert-danger', 'Le login ou l\'adresse email sont incorrects.');

        // Success
        $this->client->submitForm('send', [
            'reinit_password' => [
                'username' => $user->getUsername(),
                'email' => $user->getEmail(),
                'password' => 'Test123*',
                'confirmPassword' => 'Test123*',
            ],
        ]);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('.alert.alert-success', 'Votre mot de passe est créé !');
    }

    public function testLoginIsFailed()
    {
        $this->client = static::createClient();
        $this->client->followRedirects();
        $this->client->request('GET', '/login');

        $this->client->submitForm('send', [
            '_username' => 'badUsername',
            '_password' => 'wrongPassword',
        ]);

        static::assertSelectorExists('.alert.alert-danger');
    }

    protected function createNewUser()
    {
        /** @var Crawler */
        $crawler = $this->client->request('GET', '/admin/registration');
        $csrfToken = $crawler->filter('#user__token')->attr('value');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Création d\'un compte utilisateur');

        $this->client->request('POST', '/admin/registration', [
            'user' => [
                'firstname' => 'John',
                'lastname' => 'DOE',
                'email' => 'j.doe@mail.fr',
                'status' => User::STATUS_SOCIAL_WORKER,
                'roles' => ['ROLE_USER'],
                'username' => 'j.doe',
                '_token' => $csrfToken,
                'serviceUser' => [
                    0 => [
                        'service' => $this->data['service1'],
                    ],
                ],
            ],
        ]);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->client = null;
        $this->data = null;
    }
}
