<?php

namespace App\Tests;

use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Component\Panther\PantherTestCase;

class E2eTest extends PantherTestCase
{
    use FixturesTrait;
    use AppTestTrait;

    /** @var KernelBrowser */
    protected $client;

    // public function testHomeIsUp(): void
    // {
    //     $client = static::createPantherClient();

    //     $client->request("GET", "/");

    //     $this->assertSelectorTextContains("h1", "Merci de vous connecter");
    // }

    // public function testHomePageAfterLogin(): void
    // {

    //     $dataFixtures = $this->loadFixtureFiles([
    //         dirname(__DIR__) . "/DataFixturesTest/UserFixturesTest.yaml",
    //     ]);

    //     $user = $dataFixtures["userSuperAdmin"];

    //     $this->client = static::createPantherClient();

    //     $session = self::$container->get("session");
    //     $token = new UsernamePasswordToken($user, null, "main", $user->getRoles());
    //     $session->set("security_main", serialize($token));
    //     $session->save();
    //     $cookie = new Cookie($session->getName(), $session->getId());
    //     // $this->client->getCookieJar()->set($cookie);

    //     $crawler = $this->client->request("GET", "/");

    //     $form = $crawler->selectButton("send")->form([
    //         "_username" => "r.madelaine",
    //         "_password" => "Test123*",
    //     ]);

    //     $this->client->submit($form);

    //     $this->client->request("GET", "/person/new/");

    //     $this->assertSelectorTextContains("h1", "Tableau de bord");
    // }

    public function testLogin(): void
    {
        $this->createPantherLogin();

        $crawler = $this->client->request('GET', $this->generatePantherUri('person_new'));

        $form = $crawler->selectButton('send')->form([
            'role_person_group[person][firstname]' => 'Larissa',
            'role_person_group[person][lastname]' => 'MULLER',
            'role_person_group[person][birthdate]' => '09/05/1987',
            'role_person_group[person][gender]' => 1,
            'role_person_group[groupPeople][nbPeople]' => 1,
            'role_person_group[role]' => 5,
            'role_person_group[groupPeople][familyTypology]' => 2,
        ]);

        $this->client->submit($form);

        $this->assertSelectorExists('.alert.alert-success');
    }
}
