<?php

namespace App\Tests\Controller\Evaluation;

use App\Entity\Support\SupportGroup;
use App\Tests\AppTestTrait;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\DomCrawler\Crawler;

class EvaluationControllerTest extends WebTestCase
{
    use AppTestTrait;

    /** @var KernelBrowser */
    protected $client;

    /** @var AbstractDatabaseTool */
    protected $databaseTool;

    /** @var array */
    protected $fixtures;

    /** @var SupportGroup */
    protected $supportGroup;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = $this->createClient();

        /* @var AbstractDatabaseTool */
        $this->databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();

        $this->fixtures = $this->databaseTool->loadAliceFixture([
            dirname(__DIR__).'/../DataFixturesTest/UserFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/ServiceFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/PersonFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/SupportFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/EvaluationFixturesTest.yaml',
        ]);

        $this->supportGroup = $this->fixtures['supportGroup1'];
    }

    public function testCreateEvaluationIsSuccessful()
    {
        $this->createLogin($this->fixtures['userRoleUser']);

        $id = $this->supportGroup->getId();
        $this->client->request('GET', "/support/$id/evaluation/new");

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Évaluation sociale');
        $this->assertSelectorTextContains('.small.text-secondary', 'Créée le');
    }

    public function testCreateEvaluationIsRedirect()
    {
        $this->createLogin($this->fixtures['userRoleUser']);

        $id = $this->fixtures['supportGroupWithEval']->getId();
        /** @var Crawler */
        $crawler = $this->client->request('GET', "/support/$id/evaluation/new");

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Évaluation sociale');
        $this->assertSelectorExists('button#heading_evaluation_evaluationPeople_0_evalInitPerson');

        $csrfToken = $crawler->filter('#evaluation__token')->attr('value');

        $this->client->request('POST', "/support/$id/evaluation/edit", $this->getEvaluationData($csrfToken));

        $this->assertResponseIsSuccessful();
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('success', $content['alert']);
    }

    public function testShowEvaluationIsSuccessful()
    {
        $this->createLogin($this->fixtures['userRoleUser']);

        $id = $this->fixtures['supportGroupWithEval']->getId();
        $this->client->request('GET', "/support/$id/evaluation/view");

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Évaluation sociale');
        $this->assertSelectorExists('button#heading_evaluation_evaluationPeople_0_evalInitPerson');
    }

    public function testShowEvaluationIsRedirect()
    {
        $this->createLogin($this->fixtures['userRoleUser']);

        $id = $this->supportGroup->getId();
        $this->client->request('GET', "/support/$id/evaluation/view");

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Évaluation sociale');
        $this->assertSelectorExists('button#heading_evaluation_evaluationPeople_0_evalInitPerson');
    }

    public function testEditEvaluationIsSuccessful()
    {
        $this->createLogin($this->fixtures['userRoleUser']);

        $id = $this->fixtures['supportGroupWithEval']->getId();
        /** @var Crawler */
        $crawler = $this->client->request('GET', "/support/$id/evaluation/view");
        $csrfToken = $crawler->filter('#evaluation__token')->attr('value');

        // Fail
        $this->client->request('POST', "/support/$id/evaluation/edit");

        $this->assertResponseIsSuccessful();
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('danger', $content['alert']);

        // Success
        $this->client->request('POST', "/support/$id/evaluation/edit", $this->getEvaluationData($csrfToken));

        $this->assertResponseIsSuccessful();
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('success', $content['alert']);
    }

    public function testExportEvaluationToPdfIsSuccessful()
    {
        $this->createLogin($this->fixtures['userRoleUser']);

        // Fail
        $id = $this->supportGroup->getId();
        $this->client->request('GET', "/support/$id/evaluation/export/pdf");

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.alert.alert-warning', 'Il n\'y a pas d\'évaluation sociale créée pour ce suivi.');

        // Success export to PDF
        $id = $this->fixtures['supportGroupWithEval']->getId();
        $this->client->request('GET', "/support/$id/evaluation/export/pdf");

        $this->assertResponseIsSuccessful();
        $this->assertSame('application/pdf', $this->client->getResponse()->headers->get('content-type'));

        // Success export to Word
        $this->client->request('GET', "/support/$id/evaluation/export/word");

        $this->assertResponseIsSuccessful();
        $this->assertSame('application/vnd.ms-word', $this->client->getResponse()->headers->get('content-type'));
    }

    protected function getEvaluationData(string $csrfToken): array
    {
        return [
            'evaluation' => [
                'evalInitGroup' => [
                    'siaoRequest' => 1,
                    'socialHousingRequest' => 2,
                ],
                'evalBudgetGroup' => [
                    'cafId' => 1,
                ],
                'evalFamilyGroup' => [
                    'pmiFollowUp' => 1,
                    'famlReunification' => 1,
                    'commentEvalFamilyGroup' => 'XXX',
                ],
                'evalSocialGroup' => [
                    'reasonRequest' => 1,
                ],
                'evalHousingGroup' => [
                    'siaoRequest' => 1,
                    'siaoRequestDate' => '2020-04-03',
                    'siaoUpdatedRequestDate' => '2021-04-03',
                    'siaoRecommendation' => 10,
                    'socialHousingRequest' => 1,
                    'socialHousingRequestId' => 'XXX',
                    'socialHousingRequestDate' => '2020-04-03',
                    'socialHousingUpdatedRequestDate' => '2021-04-03',
                    'housingWishes' => 'XXX',
                    'citiesWishes' => 'XXX',
                    'syplo' => 1,
                    'syploId' => 'XXX',
                    'syploDate' => '2021-04-03',
                    'daloAction' => 1,
                    'daloType' => 1,
                    'daloDecisionDate' => '2021-04-03',
                    'daloTribunalAction' => 1,
                    'daloTribunalActionDate' => '2021-04-03',
                    'hsgActionEligibility' => 1,
                    'hsgActionRecord' => 1,
                    'hsgActionDate' => '2021-04-03',
                    'hsgActionDept' => 'XXX',
                    'hsgActionRecordId' => 'XXX',
                ],
                'evaluationPeople' => [
                    0 => [
                        'evalInitPerson' => [
                            'paper' => 1,
                            'paperType' => 1,
                            'rightSocialSecurity' => 1,
                            'socialSecurity' => 1,
                            'familyBreakdown' => 1,
                            'friendshipBreakdown' => 1,
                            'profStatus' => 1,
                            'contractType' => 1,
                            'resource' => 1,
                            'resourcesAmt' => 1100,
                            'comment' => 'XXX',
                            'evalBudgetResources' => [
                                0 => [
                                    'type' => 10, // salaire
                                    'amount' => 1000,
                                ],
                                1 => [
                                    'type' => 1000, // autre
                                    'amount' => 100,
                                    'comment' => 'Aide famille',
                                ],
                            ],
                        ],
                        'evalAdmPerson' => [
                            'nationality' => 1,
                            'paper' => 1,
                            'paperType' => 1,
                            'asylumBackground' => 1,
                            'commentEvalAdmPerson' => 'XXX',
                        ],
                        'evalBudgetPerson' => [
                            'resource' => 1,
                            'resourcesAmt' => 1100,
                            'charge' => 1,
                            'chargesAmt' => 113,
                            'debt' => 1,
                            'debtsAmt' => 3450,
                            'commentEvalBudget' => 'XXX',
                            'evalBudgetResources' => [
                                0 => [
                                    'type' => 10, // salaire
                                    'amount' => 1000,
                                ],
                                1 => [
                                    'type' => 1000, // autre
                                    'amount' => 100,
                                    'comment' => 'Aide famille',
                                ],
                            ],
                            'evalBudgetCharges' => [
                                0 => [
                                    'type' => 50, // assurance
                                    'amount' => 48,
                                ],
                                1 => [
                                    'type' => 80, // transport
                                    'amount' => 65,
                                ],
                            ],
                            'evalBudgetDebts' => [
                                0 => [
                                    'type' => 10, // dettes locatives
                                    'amount' => 3450,
                                ],
                            ],
                        ],
                        'evalFamilyPerson' => [
                            'maritalStatus' => 1,
                            'commentEvalFamilyPerson' => 'XXX',
                        ],
                        'evalProfPerson' => [
                            'profStatus' => 1,
                            'contractType' => 1,
                            'schoolLevel' => 1,
                            'profExperience' => 1,
                            'commentEvalProf' => 'XXX',
                        ],
                        'evalSocialPerson' => [
                            'rightSocialSecurity' => 1,
                            'socialSecurity' => 1,
                            'familyBreakdown' => 1,
                            'friendshipBreakdown' => 1,
                            'healthProblem' => 1,
                            'commentEvalSocialPerson' => 'XXX',
                        ],
                    ],
                ],
                '_token' => $csrfToken,
            ],
        ];
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->client = null;
        $this->fixtures = null;

        $cache = new FilesystemAdapter($_SERVER['DB_DATABASE_NAME']);
        $cache->clear();
    }
}
