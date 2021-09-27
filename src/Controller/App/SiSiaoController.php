<?php

namespace App\Controller\App;

use App\Entity\Support\SupportGroup;
use App\Event\Evaluation\EvaluationEvent;
use App\Form\Admin\Security\SiSiaoLoginType;
use App\Form\Model\SiSiao\SiSiaoLogin;
use App\Service\SiSiao\SiSiaoEvaluationImporter;
use App\Service\SiSiao\SiSiaoGroupImporter;
use App\Service\SiSiao\SiSiaoRequest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SiSiaoController extends AbstractController
{
    protected $siSiaoRequest;

    public function __construct(SiSiaoRequest $siSiaoRequest)
    {
        $this->siSiaoRequest = $siSiaoRequest;
    }

    /**
     * @Route("/api-sisiao/login", name="api_sisiao_login", methods="POST")
     */
    public function login(Request $request): Response
    {
        $form = $this->createForm(SiSiaoLoginType::class, $siSiaoUser = new SiSiaoLogin())
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $response = $this->siSiaoRequest->login($siSiaoUser);

            if ($response['isConnected']) {
                return $this->json([
                    'alert' => 'success',
                    'msg' => 'Connexion SI-SIAO réussie.',
                ]);
            }
        }

        return $this->json([
            'alert' => 'danger',
            'msg' => 'Identifiant ou mot de passe incorrect.',
            'code' => Response::HTTP_UNPROCESSABLE_ENTITY,
        ]);
    }

    /**
     * @Route("/api-sisiao/check-connection", name="api_sisiao_check_connection", methods="POST")
     */
    public function checkConnection(): Response
    {
        if (false === $this->siSiaoRequest->isConnected()) {
            return $this->json(['isConnected' => false]);
        }

        return $this->json([
            'isConnected' => true,
            'alert' => 'success',
            'msg' => 'Connexion SI-SIAO réussie.',
        ]);
    }

    /**
     * Search group by id in API SI-SIAO.
     *
     * @Route("/api-sisiao/find-group/{id}", name="api_sisiao_find_group", methods="GET")
     * @IsGranted("ROLE_SUPER_ADMIN")
     */
    public function findGroup(string $id): Response
    {
        $result = $this->siSiaoRequest->findGroupById($id);

        return $this->json($result);
    }

    /**
     * Search group by id in API SI-SIAO.
     *
     * @Route("/api-sisiao/search-group/{id}", name="api_sisiao_search_group", methods="GET")
     */
    public function searchGroup(string $id): Response
    {
        return $this->json([
            'people' => $this->siSiaoRequest->findPeople($id),
        ]);
    }

    /**
     * Show group by id in API SI-SIAO.
     *
     * @Route("/api-sisiao/show-group/{id}", name="api_sisiao_show_group", methods="GET")
     */
    public function showGroup(string $id): Response
    {
        $data = $this->siSiaoRequest->get("fiches/ficheIdentite/{$id}");
        if (null === $data) {
            return $this->json([
                'alert' => 'warning',
                'msg' => 'Aucun résultat.',
            ]);
        }

        $dp = $data->demandeurprincipal;

        if ('Personne' === $data->typefiche) {
            foreach ($dp->fiches as $fiche) {
                if ('Groupe' === $fiche->typefiche) {
                    $personnes = $fiche->personnes;
                    $idGroupe = $fiche->id;
                }
            }
            if (!isset($personnes)) {
                $fiche = $dp->fiches[count($dp->fiches) - 1];
                $personnes = $fiche->personnes;
                $idGroupe = $fiche->id;
            }
        } else {
            $personnes = $data->personnes;
            $idGroupe = $data->id;
        }

        return $this->json([
            'html' => $this->render('app/admin/siSiao/_siSiaoGroup.html.twig', [
                'composition' => $data->composition,
                'dp' => $dp,
                'personnes' => $personnes,
                'idGroupe' => $idGroupe,
            ]),
            'alert' => 'success',
            'idGroup' => $idGroupe,
        ]);
    }

    /**
     * Search group by id in API SI-SIAO.
     *
     * @Route("/api-sisiao/import-group/{id}", name="api_sisiao_import_group", methods="GET")
     */
    public function importGroup(SiSiaoGroupImporter $siSiaoGroupImporter, string $id): Response
    {
        $peopleGroup = $siSiaoGroupImporter->import((int) $id);

        if (!$peopleGroup) {
            return $this->redirectToRoute('new_support_search_person');
        }

        return $this->redirectToRoute('people_group_show', ['id' => $peopleGroup->getId()]);
    }

    /**
     * Search group by id in API SI-SIAO.
     *
     * @Route("/api-sisiao/import-evaluation/{id}", name="api_sisiao_import_evaluation", methods="GET")
     */
    public function importEvaluation(
        SupportGroup $supportGroup,
        SiSiaoEvaluationImporter $siSiaoEvalImporter,
        EventDispatcherInterface $dispatcher
    ): Response {
        $evaluationGroup = $siSiaoEvalImporter->import($supportGroup);

        if ($evaluationGroup) {
            $dispatcher->dispatch(new EvaluationEvent($evaluationGroup), 'evaluation.after_update');

            $this->addFlash('success', "L'évaluation sociale a été importée.");
        }

        return $this->redirectToRoute('support_view', ['id' => $supportGroup->getId()]);
    }

    /**
     * @Route("/api-sisiao/update-evaluation/{id}", name="api_sisiao_update_evaluation", methods="GET")
     */
    public function updateEvaluation(string $id): Response
    {
        $result = $this->siSiaoRequest->updateEvaluation($id);

        return $this->json($result);
    }

    /**
     * @Route("/api-sisiao/update-siao-request/{id}", name="api_sisiao_update_siao_request", methods="GET")
     */
    public function updateSiaoRequest(string $id): Response
    {
        $result = $this->siSiaoRequest->updateSiaoRequest($id);

        return $this->json($result);
    }

    /**
     * Get informations of connected user.
     *
     * @Route("/api-sisiao/user", name="api_sisiao_user", methods="GET")
     */
    public function getUser(): Response
    {
        return $this->json($this->siSiaoRequest->getUser());
    }

    /**
     * Get all referentiels items in SI-SIAO.
     *
     * @Route("/api-sisiao/referentiels", name="api_sisiao_referentiels", methods="GET")
     * @IsGranted("ROLE_SUPER_ADMIN")
     */
    public function getReferentiels(): Response
    {
        $results = $this->siSiaoRequest->getReferentielsToString();

        return new Response($results);
    }

    /**
     * @Route("/api-sisiao/logout", name="api_sisiao_logout", methods="GET")
     */
    public function logout(): Response
    {
        $this->siSiaoRequest->logout();

        $this->addFlash('success', 'Vous êtes déconnecté du SI-SIAO.');

        return $this->redirectToRoute('home');
    }
}
