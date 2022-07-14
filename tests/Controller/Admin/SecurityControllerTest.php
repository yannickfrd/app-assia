<?php

namespace App\Tests\Controller\Admin;

use App\Entity\Organization\User;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

class SecurityControllerTest extends WebTestCase
{
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

        $this->client = static::createClient();
        $this->client->followRedirects();

        /* @var AbstractDatabaseTool */
        $this->databaseTool = $this->getContainer()->get(DatabaseToolCollection::class)->get();

        $this->fixtures = $this->databaseTool->loadAliceFixture([
            dirname(__DIR__).'/../fixtures/app_fixtures_test.yaml',
        ]);
    }

    public function testLoginIsSuccessful(): void
    {
        $this->client->loginUser($this->fixtures['john_user']);
        $this->client->followRedirects();

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
        $this->client->loginUser($this->fixtures['user_admin']);

        /** @var Crawler */
        $crawler = $this->client->request('GET', '/admin/registration');
        $csrfToken = $crawler->filter('#user__token')->attr('value');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Création d\'un compte utilisateur');

        $this->client->request('POST', '/admin/registration', [
            'user' => [
                'firstname' => 'John',
                'lastname' => 'DOE',
                'email' => 'j.doe@mail.fr',
                'status' => User::STATUS_SOCIAL_WORKER,
                'roles' => ['ROLE_USER'],
                'username' => '',
                '_token' => $csrfToken,
            ],
        ]);

        $this->assertResponseStatusCodeSame(422);
    }

    public function testRegistrationIsSuccessful(): void
    {
        $admin = $this->fixtures['user_admin'];
        $this->client->loginUser($admin);

        $this->createNewUser($admin);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.toast.alert-success', 'Le compte de John est créé.');
    }

    public function testSendNewEmailToUser(): void
    {
        $this->client->loginUser($this->fixtures['user_admin']);

        $user = $this->fixtures['john_user'];

        $id = $user->getId();
        $this->client->request('GET', "/admin/user/$id/send_new_email");

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', $user->getFullname());
    }

    public function testInitPasswordIsSuccessful(): void
    {
        $this->client->loginUser($this->fixtures['john_user']);
        $this->client->request('GET', '/login/init_password');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Personnalisation du mot de passe');

        $this->client->submitForm('send', [
            'init_password' => [
                'password' => 'Password123!',
                'confirmPassword' => 'Password123!',
            ],
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.toast.alert-success', 'Votre mot de passe est mis à jour !');
    }

    public function testEditCurrentUserIsSuccessful(): void
    {
        $user = $this->fixtures['john_user'];

        $this->client->loginUser($user);
        $this->client->request('GET', '/my_profile');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('h1', $user->getFullname());

        $this->client->submitForm('send', [
            'user' => [
                'email' => 'j.doe@mail.fr',
                'phone1' => '0102030405',
                'phone2' => '0602030405',
            ],
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.toast.alert-success', 'Les modifications sont enregistrées.');

        $this->client->submitForm('send2', [
            'change_password' => [
                'oldPassword' => 'Password123!',
                'newPassword' => 'Pa$$word123!',
                'confirmNewPassword' => 'Pa$$word123!',
            ],
        ]);
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.toast.alert-danger', 'Le mot de passe ou la confirmation sont invalides.');

        $this->client->submitForm('send2', [
            'change_password' => [
                'oldPassword' => 'password',
                'newPassword' => 'Password123!',
                'confirmNewPassword' => 'Password123!',
            ],
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.toast.alert-success', 'Votre mot de passe est mis à jour !');
    }

    public function testEditUserIsSuccessful(): void
    {
        $user = $this->fixtures['user_admin'];
        $this->client->loginUser($user);

        $id = $user->getId();
        $this->client->request('GET', "/admin/user/$id");

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', $user->getFullname());

        $this->client->submitForm('send', [
            'user' => [
                'email' => 'j.doe@mail.fr',
                'phone1' => '0102030405',
            ],
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.toast.alert-success', 'Les modifications sont enregistrées.');
    }

    public function testDisableUserIsSuccessful(): void
    {
        $user = $this->fixtures['user_super_admin'];
        $this->client->loginUser($user);

        $id = $user->getId();
        $this->client->request('GET', "/admin/user/$id/disable");

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.toast.alert-danger', 'Vous ne pouvez pas vous-même désactiver votre compte utilisateur.');

        $id = $this->fixtures['john_user']->getId();
        $this->client->request('GET', "/admin/user/$id/disable");

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.toast.alert-warning', 'Ce compte utilisateur est désactivé.');

        $this->client->request('GET', "/admin/user/$id/disable");

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.toast.alert-success', 'Ce compte utilisateur est ré-activé.');
    }

    public function testForgotPasswordIsSuccessful(): void
    {
        /** @var User */
        $user = $this->fixtures['john_user'];

        $this->client->followRedirects();
        $this->client->request('GET', '/login/forgot_password');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Mot de passe oublié');

        // Fail
        $this->client->submitForm('send', [
            'forgot_password' => [
                'username' => 'bad_username',
                'email' => 'bad_username@mail.fr',
            ],
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.toast.alert-danger', 'Le login ou l\'adresse email sont incorrects.');

        // Success
        $this->client->submitForm('send', [
            'forgot_password' => [
                'username' => $user->getUsername(),
                'email' => $user->getEmail(),
            ],
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.toast.alert-success', 'Un mail vous a été envoyé');
    }

    public function testReinitPasswordIsSuccessful(): void
    {
        /** @var User */
        $user = $this->fixtures['john_user'];

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

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Réinitialisation du mot de passe');

        $this->client->submitForm('send', [
            'reinit_password' => [
                'username' => $user->getUsername(),
                'email' => $user->getEmail(),
                'password' => 'Password123!',
                'confirmPassword' => 'Password123!',
            ],
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.toast.alert-danger', 'Le login ou l\'adresse email sont incorrects.');

        // Success with good token
        $this->client->request('GET', '/login/reinit_password/'.$user->getToken());

        $this->client->submitForm('send', [
            'reinit_password' => [
                'username' => $user->getUsername(),
                'email' => $user->getEmail(),
                'password' => 'Password123!',
                'confirmPassword' => 'Password123!',
            ],
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.toast.alert-success', 'Votre mot de passe est réinitialisé !');
    }

    public function testCreatePasswordIsSuccessful(): void
    {
        $admin = $this->fixtures['user_admin'];
        $this->client->loginUser($admin);

        $this->createNewUser($admin);
        $this->client->request('GET', '/logout');

        $userRepo = $this->client->getContainer()->get('doctrine.orm.entity_manager')->getRepository(User::class);
        /** @var User */
        $user = $userRepo->findOneBy(['username' => 'j.doe']);

        // Fail with invalid token
        $this->client->request('GET', '/login/create_password/token');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.toast.alert-danger', 'Le lien est expiré ou invalide.');

        // Fail with valid token
        $this->client->request('GET', '/login/create_password/'.$user->getToken());

        $this->client->submitForm('send', [
            'reinit_password' => [
                'username' => 'bad_login',
                'email' => $user->getEmail(),
                'password' => 'Password123!',
                'confirmPassword' => 'Password123!',
            ],
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.toast.alert-danger', 'Le login ou l\'adresse email sont incorrects.');

        // Success
        $this->client->submitForm('send', [
            'reinit_password' => [
                'username' => $user->getUsername(),
                'email' => $user->getEmail(),
                'password' => 'Password123!',
                'confirmPassword' => 'Password123!',
            ],
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.toast.alert-success', 'Votre mot de passe est créé !');
    }

    public function testLoginIsFailed(): void
    {
        $this->client->followRedirects();
        $this->client->request('GET', '/login');

        $this->client->submitForm('send', [
            'username' => 'badUsername',
            'password' => 'wrongPassword',
        ]);

        static::assertSelectorExists('.toast.alert-danger');
    }

    protected function createNewUser(User $admin = null): void
    {
        /** @var Crawler */
        $crawler = $this->client->request('GET', '/admin/registration');
        $csrfToken = $crawler->filter('#user__token')->attr('value');

        $this->assertResponseIsSuccessful();
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
                        'service' => $admin ? $admin->getServiceUser()->first()->getId() : $this->fixtures['service1'],
                    ],
                ],
            ],
        ]);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
