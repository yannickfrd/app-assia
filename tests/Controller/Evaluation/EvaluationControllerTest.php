<?php

namespace App\Tests\Controller\Evaluation;

use App\Entity\Support\SupportGroup;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Response;

class EvaluationControllerTest extends WebTestCase
{
    /** @var KernelBrowser */
    protected $client;

    /** @var array */
    protected $fixtures;

    /** @var SupportGroup */
    protected $supportGroup;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();
        $this->client->followRedirects();

        /** @var AbstractDatabaseTool $databaseTool */
        $databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();

        $this->fixtures = $databaseTool->loadAliceFixture([
            dirname(__DIR__).'/../fixtures/app_fixtures_test.yaml',
            dirname(__DIR__).'/../fixtures/service_fixtures_test.yaml',
            dirname(__DIR__).'/../fixtures/person_fixtures_test.yaml',
            dirname(__DIR__).'/../fixtures/support_fixtures_test.yaml',
            dirname(__DIR__).'/../fixtures/evaluation_fixtures_test.yaml',
        ]);

        $this->supportGroup = $this->fixtures['support_group1'];
    }

    public function testCreateEvaluationIsSuccessful(): void
    {
        $this->client->loginUser($this->fixtures['john_user']);

        $id = $this->supportGroup->getId();
        $this->client->request('GET', "/support/$id/evaluation/new");

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Évaluation sociale');
        $this->assertSelectorTextContains('.small.text-secondary', 'Créée le');
    }

    public function testCreateEvaluationIsRedirect(): void
    {
        $this->client->loginUser($this->fixtures['john_user']);

        $id = $this->fixtures['support_group_with_eval']->getId();
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

    public function testShowEvaluationIsSuccessful(): void
    {
        $this->client->loginUser($this->fixtures['john_user']);

        $id = $this->fixtures['support_group_with_eval']->getId();
        $this->client->request('GET', "/support/$id/evaluation/show");

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Évaluation sociale');
        $this->assertSelectorExists('button#heading_evaluation_evaluationPeople_0_evalInitPerson');
    }

    public function testShowEvaluationIsRedirect(): void
    {
        $this->client->loginUser($this->fixtures['john_user']);

        $id = $this->supportGroup->getId();
        $this->client->request('GET', "/support/$id/evaluation/show");

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Évaluation sociale');
        $this->assertSelectorExists('button#heading_evaluation_evaluationPeople_0_evalInitPerson');
    }

    public function testEditEvaluationIsSuccessful(): void
    {
        $this->client->loginUser($this->fixtures['john_user']);

        $id = $this->fixtures['support_group_with_eval']->getId();
        /** @var Crawler */
        $crawler = $this->client->request('GET', "/support/$id/evaluation/show");
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

    public function testExportEvaluationToPdfIsSuccessful(): void
    {
        $this->client->loginUser($this->fixtures['john_user']);

        // Fail
        $id = $this->supportGroup->getId();
        $this->client->request('GET', "/support/$id/evaluation/export/pdf");

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.toast.alert-warning', 'Il n\'y a pas d\'évaluation sociale créée pour ce suivi.');

        // Success export to PDF
        $id = $this->fixtures['support_group_with_eval']->getId();
        $this->client->request('GET', "/support/$id/evaluation/export/pdf");

        $this->assertResponseIsSuccessful();
        $this->assertSame('application/pdf', $this->client->getResponse()->headers->get('content-type'));

        // Success export to Word
        $this->client->request('GET', "/support/$id/evaluation/export/word");

        $this->assertResponseIsSuccessful();
        $this->assertSame('application/vnd.ms-word', $this->client->getResponse()->headers->get('content-type'));
    }

    public function testDeleteEvaluationIsFailed(): void
    {
        $this->client->loginUser($this->fixtures['john_user']);

        $id = $this->fixtures['evaluation_group1']->getId();
        $this->client->request('GET', "/evaluation/$id/delete");

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testDeleteEvaluationIsSuccessful(): void
    {
        $this->client->loginUser($this->fixtures['user_super_admin']);

        $id = $this->fixtures['evaluation_group1']->getId();
        $this->client->request('GET', "/evaluation/$id/delete");

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.toast.alert-warning', "L'évaluation sociale a été supprimée.");
    }

    public function testFixPeopleIsSuccessful(): void
    {
        $this->client->loginUser($this->fixtures['john_user']);

        $id = $this->fixtures['evaluation_group1']->getId();
        $this->client->request('GET', "/evaluation/$id/fix-people");

        $this->assertResponseIsSuccessful();
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
                    'siaoRecommendation' => 104,
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

        $cache = new FilesystemAdapter($_SERVER['DB_DATABASE_NAME']);
        $cache->clear();
    }
}
