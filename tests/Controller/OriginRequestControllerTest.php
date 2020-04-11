<?php

namespace App\Tests\Controller;

use App\Tests\AppTestTrait;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class OriginRequestControllerTest extends WebTestCase
{
    use FixturesTrait;
    use AppTestTrait;

    /** @var KernelBrowser */
    protected $client;

    /** @var array */
    protected $dataFixtures;

    /** @var SupportGroup */
    protected $supportGroup;

    protected function setUp()
    {
        $this->dataFixtures = $this->loadFixtureFiles([
            dirname(__DIR__).'/DataFixturesTest/SupportFixturesTest.yaml',
        ]);

        $this->createLogin($this->dataFixtures['userSuperAdmin']);

        $this->supportGroup = $this->dataFixtures['supportGroup1'];
    }

    public function testEditOriginRequestIsUp()
    {
        $this->client->request('POST', $this->generateUri('support_originRequest', [
            'id' => $this->supportGroup->getId(),
        ]));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Origine de la demande');
    }

    public function testEditOriginRequestIsSuccessful()
    {
        /** @var Crawler */
        $crawler = $this->client->request('POST', $this->generateUri('support_originRequest', [
            'id' => $this->supportGroup->getId(),
        ]));

        $faker = \Faker\Factory::create('fr_FR');
        $now = new \DateTime();

        $form = $crawler->selectButton('send')->form([
            'origin_request[organization]' => 1,
            'origin_request[organizationComment]' => $faker->sentence(mt_rand(3, 6), true),
            'origin_request[preAdmissionDate]' => $now->format('Y-m-d'),
            'origin_request[resulPreAdmission]' => 1,
            'origin_request[decisionDate]' => $now->format('Y-m-d'),
            'origin_request[comment]' => $faker->paragraphs(6, true),
        ]);

        $this->client->submit($form);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Origine de la demande');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->client = null;
        $this->dataFixtures = null;
    }
}
