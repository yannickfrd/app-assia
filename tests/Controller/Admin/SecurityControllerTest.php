<?php

namespace App\Tests\Controller\Admin;

use App\Tests\AppTestTrait;
use App\Entity\Organization\User;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;

class SecurityControllerTest extends WebTestCase
{
    use AppTestTrait;

    /** @var KernelBrowser */
    protected $client;

    /** @var AbstractDatabaseTool */
    protected $databaseTool;

    /** @var array */
    protected $fixtures;

    /** @var User */
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->client = $this->createClient();

        /** @var AbstractDatabaseTool */
        $this->databaseTool = $this->getContainer()->get(DatabaseToolCollection::class)->get();

        $this->fixtures = $this->databaseTool->loadAliceFixture([
            dirname(__DIR__).'/../DataFixturesTest/UserFixturesTest.yaml',
        ]);
    }

    public function testLoginIsSuccessful(): void
    {
        $this->createLogin($this->fixtures['userRoleUser']);
        // $this->client->followRedirects();

        // $this->client->request('GET', '/login');

        // static::assertResponseStatusCodeSame(Response::HTTP_OK);
        // static::assertSelectorTextContains('h1', 'Merci de vous connecter');

        // $csrfToken = $this->client->getContainer()->get('security.csrf.token_manager')->getToken('authenticate');

        // $this->client->request('POST', '/login', [
        //     'username' => 'r.user',
        //     'password' => 'Test123*',
        //     '_csrf_token' => $csrfToken,
        // ]);

        // static::assertSelectorExists('.alert.alert-success');

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
        $this->client->request('GET', '/logout');
        static::assertSelectorTextContains('h1', 'Merci de vous connecter');
    }

    public function testRegistrationIsFailed(): void
    {
        $this->createLogin($this->fixtures['userAdmin']);

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

    public function testRegistrationIsSuccessful(): void
    {
        $this->createLogin($this->fixtures['userAdmin']);

        $this->createNewUser();

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('.alert.alert-success', 'Le compte de John est créé');
    }

    public function testSendNewEmailToUser(): void
    {
        $this->createLogin($this->fixtures['userAdmin']);

        $user = $this->fixtures['userRoleUser'];

        $id = $user->getId();
        $this->client->request('GET', "/admin/user/$id/send_new_email");

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', $user->getFullname());
    }

    public function testInitPasswordIsSuccessful(): void
    {
        $this->createLogin($this->fixtures['userRoleUser']);
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

    public function testEditCurrentUserIsSuccessful(): void
    {
        $user = $this->fixtures['userRoleUser'];

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

    public function testEditUserIsSuccessful(): void
    {
        $user = $this->fixtures['userAdmin'];
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

    public function testDisableUserIsSuccessful(): void
    {
        $user = $this->fixtures['userAdmin'];
        $this->createLogin($user);

        $id = $user->getId();
        $this->client->request('GET', "/admin/user/$id/disable");

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorExists('.alert.alert-danger', 'Vous ne pouvez pas vous-même désactiver votre compte utilisateur.');

        $id = $this->fixtures['userRoleUser']->getId();
        $this->client->request('GET', "/admin/user/$id/disable");

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorExists('.alert.alert-warning', 'Ce compte utilisateur est désactivé.');

        $this->client->request('GET', "/admin/user/$id/disable");

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorExists('.alert.alert-success', 'Ce compte utilisateur est ré-activé.');
    }

    public function testForgotPasswordIsSuccessful(): void
    {
        /** @var User */
        $user = $this->fixtures['userRoleUser'];

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

    public function testReinitPasswordIsSuccessful(): void
    {
        /** @var User */
        $user = $this->fixtures['userRoleUser'];

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

    public function testCreatePasswordIsSuccessful(): void
    {
        $this->createLogin($this->fixtures['userAdmin']);

        $this->createNewUser();
        $this->client->request('GET', '/logout');

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

    public function testLoginIsFailed(): void
    {
        $this->client->followRedirects();
        $this->client->request('GET', '/login');

        $this->client->submitForm('send', [
            'username' => 'badUsername',
            'password' => 'wrongPassword',
        ]);

        static::assertSelectorExists('.alert.alert-danger');
    }

    protected function createNewUser(): void
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
                        'service' => $this->fixtures['service1'],
                    ],
                ],
            ],
        ]);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        
        $this->client = null;
        $this->fixtures = null;
    }
}
