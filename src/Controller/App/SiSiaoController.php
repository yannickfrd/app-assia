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
use Symfony\Component\HttpFoundation\JsonResponse;
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
    public function login(Request $request): JsonResponse
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
            ],
            Response::HTTP_UNPROCESSABLE_ENTITY,
        );
    }

    /**
     * @Route("/api-sisiao/check-connection", name="api_sisiao_check_connection", methods="GET")
     */
    public function checkConnection(): JsonResponse
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
     * @Route("/api-sisiao/search-group/{id}", name="api_sisiao_search_group", methods="GET")
     */
    public function searchGroup(int $id): JsonResponse
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
    public function showGroup(int $id): JsonResponse
    {
        $data = $this->siSiaoRequest->findGroupById($id);

        if (is_array($data) && 'success' === $data['alert']) {
            return $this->json([
                'alert' => 'success',
                'html' => $this->render('app/admin/siSiao/_siSiaoGroup.html.twig', $data['group']),
                'idGroup' => $data['group']['idGroupe'],
            ]);
        }

        return $this->json($data);
    }

    /**
     * Search group by id in API SI-SIAO.
     *
     * @Route("/api-sisiao/import-group/{id}", name="api_sisiao_import_group", methods="GET")
     */
    public function importGroup(int $id, SiSiaoGroupImporter $siSiaoGroupImporter): Response
    {
        $peopleGroup = $siSiaoGroupImporter->import($id);

        if ($peopleGroup) {
            return $this->redirectToRoute('people_group_show', ['id' => $peopleGroup->getId()]);
        }

        return $this->redirectToRoute('new_support_search_person');
    }

    /**
     * Search group by id in API SI-SIAO.
     *
     * @Route("/api-sisiao/support/{id}/import-evaluation", name="api_sisiao_support_import_evaluation", methods="GET")
     */
    public function importEvaluation(
        SupportGroup $supportGroup,
        SiSiaoEvaluationImporter $siSiaoEvalImporter,
        EventDispatcherInterface $dispatcher
    ): Response {
        $this->denyAccessUnlessGranted('EDIT', $supportGroup);

        if ($evaluationGroup = $siSiaoEvalImporter->import($supportGroup)) {
            $dispatcher->dispatch(new EvaluationEvent($evaluationGroup), 'evaluation.after_update');

            $this->addFlash('success', "L'évaluation sociale a été importée.");
        }

        return $this->redirectToRoute('support_view', ['id' => $supportGroup->getId()]);
    }

    /**
     * Get informations of connected user.
     *
     * @Route("/api-sisiao/user", name="api_sisiao_user", methods="GET")
     * @IsGranted("ROLE_SUPER_ADMIN")
     */
    public function getUser(): JsonResponse
    {
        return $this->json($this->siSiaoRequest->getUser());
    }

    /**
     * @Route("/api-sisiao/logout", name="api_sisiao_logout", methods="GET")
     */
    public function logout(): JsonResponse
    {
        $this->siSiaoRequest->logout();

        return $this->json([
            'alert' => 'success',
            'msg' => 'Vous avez bien été déconnecté du SI-SIAO.',
        ]);
    }

    /**
     * Get all referentiels items in SI-SIAO.
     *
     * @Route("/api-sisiao/referentiels", name="api_sisiao_referentiels", methods="GET")
     * @IsGranted("ROLE_SUPER_ADMIN")
     */
    public function getReferentiels(): Response
    {
        return new Response($this->siSiaoRequest->getReferentielsToString());
    }

    // /**
    //  * @Route("/api-sisiao/update-evaluation/{id}", name="api_sisiao_update_evaluation", methods="GET")
    //  */
    // public function updateEvaluation(int $id): JsonResponse
    // {
    //     return $this->json($this->siSiaoRequest->updateEvaluation($id));
    // }

    // /**
    //  * @Route("/api-sisiao/update-siao-request/{id}", name="api_sisiao_update_siao_request", methods="GET")
    //  */
    // public function updateSiaoRequest(int $id): JsonResponse
    // {
    //     return $this->json($this->siSiaoRequest->updateSiaoRequest($id));
    // }

    // /**
    //  * Search group by id in API SI-SIAO.
    //  *
    //  * @Route("/api-sisiao/dump-group/{id}", name="api_sisiao_dump_group", methods="GET")
    //  * @IsGranted("ROLE_SUPER_ADMIN")
    //  */
    // public function dumpGroup(int $id): JsonResponse
    // {
    //     return $this->json($this->siSiaoRequest->dumpGroupById($id));
    // }
}
