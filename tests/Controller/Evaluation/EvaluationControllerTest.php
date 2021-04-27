<?php

namespace App\Tests\Controller\Evaluation;

use App\Entity\Support\SupportGroup;
use App\Tests\AppTestTrait;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Response;

class EvaluationControllerTest extends WebTestCase
{
    use FixturesTrait;
    use AppTestTrait;

    /** @var KernelBrowser */
    protected $client;

    /** @var array */
    protected $data;

    /** @var SupportGroup */
    protected $supportGroup;

    protected function setUp()
    {
        $this->data = $this->loadFixtureFiles([
            dirname(__DIR__).'/../DataFixturesTest/UserFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/ServiceFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/PersonFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/SupportFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/EvaluationFixturesTest.yaml',
        ]);

        $this->supportGroup = $this->data['supportGroup1'];
    }

    public function testCreateEvaluationIsSuccessful()
    {
        $this->createLogin($this->data['userRoleUser']);

        $id = $this->supportGroup->getId();
        $this->client->request('GET', "/support/$id/evaluation/new");

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Évaluation sociale');
        $this->assertSelectorTextContains('.small.text-secondary', 'Évaluation créée le');
    }

    public function testCreateEvaluationIsRedirect()
    {
        $this->createLogin($this->data['userRoleUser']);

        $id = $this->data['supportGroupWithEval']->getId();
        /** @var Crawler */
        $crawler = $this->client->request('GET', "/support/$id/evaluation/new");

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Évaluation sociale');
        $this->assertSelectorExists('button#heading-init_eval-0');

        $csrfToken = $crawler->filter('#evaluation__token')->attr('value');

        $this->client->request('POST', "/support/$id/evaluation/edit", $this->getEvaluationData($csrfToken));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('success', $content['alert']);
    }

    public function testShowEvaluationIsSuccessful()
    {
        $this->createLogin($this->data['userRoleUser']);

        $id = $this->data['supportGroupWithEval']->getId();
        $this->client->request('GET', "/support/$id/evaluation/view");

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Évaluation sociale');
        $this->assertSelectorExists('button#heading-init_eval-0');
    }

    public function testShowEvaluationIsRedirect()
    {
        $this->createLogin($this->data['userRoleUser']);

        $id = $this->supportGroup->getId();
        $this->client->request('GET', "/support/$id/evaluation/view");

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Évaluation sociale');
        $this->assertSelectorExists('button#heading-init_eval-0');
    }

    public function testEditEvaluationIsSuccessful()
    {
        $this->createLogin($this->data['userRoleUser']);

        $id = $this->data['supportGroupWithEval']->getId();
        /** @var Crawler */
        $crawler = $this->client->request('GET', "/support/$id/evaluation/view");
        $csrfToken = $crawler->filter('#evaluation__token')->attr('value');

        // Fail
        $this->client->request('POST', "/support/$id/evaluation/edit");

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('danger', $content['alert']);

        // Success
        $this->client->request('POST', "/support/$id/evaluation/edit", $this->getEvaluationData($csrfToken));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('success', $content['alert']);
    }

    public function testExportEvaluationToPdfIsSuccessful()
    {
        $this->createLogin($this->data['userRoleUser']);

        // Fail
        $id = $this->supportGroup->getId();
        $this->client->request('GET', "/support/$id/evaluation/export/pdf");

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('.alert.alert-warning', 'Il n\'y a pas d\'évaluation sociale créée pour ce suivi.');

        // Success export to PDF
        $id = $this->data['supportGroupWithEval']->getId();
        $this->client->request('GET', "/support/$id/evaluation/export/pdf");

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSame('application/pdf', $this->client->getResponse()->headers->get('content-type'));

        // Success export to Word
        $this->client->request('GET', "/support/$id/evaluation/export/word");

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSame('application/vnd.ms-word', $this->client->getResponse()->headers->get('content-type'));
    }

    protected function getEvaluationData(string $csrfToken)
    {
        return [
            'evaluation' => [
                'initEvalGroup' => [
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
                        'initEvalPerson' => [
                            'paper' => 1,
                            'paperType' => 1,
                            'rightSocialSecurity' => 1,
                            'socialSecurity' => 1,
                            'familyBreakdown' => 1,
                            'friendshipBreakdown' => 1,
                            'profStatus' => 1,
                            'contractType' => 1,
                            'resources' => [
                                'resources' => 1,
                                'resourcesAmt' => 1100,
                                'salary' => 1,
                                'salaryAmt' => 1000,
                                'ressourceOther' => 1,
                                'ressourceOtherPrecision' => 'Aide famille',
                                'ressourceOtherAmt' => 100,
                            ],
                            'debts' => 1,
                            'debtsAmt' => 1000,
                            'comment' => 'XXX',
                        ],
                        'evalAdmPerson' => [
                            'nationality' => 1,
                            'paper' => 1,
                            'paperType' => 1,
                            'asylumBackground' => 1,
                            'commentEvalAdmPerson' => 'XXX',
                        ],
                        'evalBudgetPerson' => [
                            'resources' => [
                                'resources' => 1,
                                'resourcesAmt' => 1100,
                                'salary' => 1,
                                'salaryAmt' => 1000,
                                'ressourceOther' => 1,
                                'ressourceOtherPrecision' => 'Aide famille',
                                'ressourceOtherAmt' => 100,
                            ],
                            'debts' => 1,
                            'debtsAmt' => 1000,
                            'commentEvalBudget' => 'XXX',
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
        $this->data = null;

        $cache = new FilesystemAdapter($_SERVER['DB_DATABASE_NAME']);
        $cache->clear();
    }
}
