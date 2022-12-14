<?php

declare(strict_types=1);

namespace App\Controller\Support;

use App\Entity\Support\SupportGroup;
use App\Entity\Support\SupportPerson;
use App\Form\Support\Support\AddPersonToSupportType;
use App\Repository\Support\SupportPersonRepository;
use App\Service\Grammar;
use App\Service\SupportGroup\SupportManager;
use App\Service\SupportGroup\SupportPeopleAdder;
use App\Service\SupportGroup\SupportRestorer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class SupportPersonController extends AbstractController
{
    /**
     * Ajout de nouvelles personnes au suivi.
     *
     * @Route("/support/{id}/add_person", name="support_add_person", methods="POST")
     */
    public function addPersonToSupport(
        SupportGroup $supportGroup,
        Request $request,
        SupportPeopleAdder $supportPeopleAdder,
        SupportManager $supportManager
    ): Response {
        $this->denyAccessUnlessGranted('EDIT', $supportGroup);

        $form = $this->createForm(AddPersonToSupportType::class, null, [
            'attr' => ['supportGroup' => $supportGroup],
        ])->handleRequest($request);

        $rolePerson = $form->get('rolePerson')->getData();

        if ($rolePerson && $supportPeopleAdder->addPersonToSupport($supportGroup, $rolePerson)) {
            $supportManager->update($supportGroup);
        }

        return $this->redirectToRoute('support_edit', ['id' => $supportGroup->getId()]);
    }

    /**
     * Supprime la personne du suivi social.
     *
     * @Route("/support-person/{id}/delete/{_token}", name="support_peron_delete", methods="GET")
     */
    public function delete(
        SupportPerson $supportPerson,
        SupportManager $supportManager,
        TranslatorInterface $translator,
        string $_token
    ): Response {
        $error = false;

        $supportGroup = $supportPerson->getSupportGroup();

        $this->denyAccessUnlessGranted('EDIT', $supportGroup);

        // V??rifie si le token est valide avant de retirer la personne du suivi social
        if (!$this->isCsrfTokenValid('remove'.$supportPerson->getId(), $_token)) {
            $error = true;
            $this->addFlash('danger', 'error_occurred');
        }
        // V??rifie si la personne est le demandeur principal
        if ($supportPerson->getHead()) {
            $error = true;
            $this->addFlash('danger', 'support_group.header_cannot_be_removed');
        }
        // V??rifie si le nombre de personne dans le suivi est sup??rieur ?? 1
        if ($supportGroup->getSupportPeople()->count() <= 1) {
            $error = true;
            $this->addFlash('danger', 'support_group.error_minimum_people');
        }

        if (false === $error) {
            try {
                $supportGroup->removeSupportPerson($supportPerson);

                $supportManager->update($supportGroup);

                $this->addFlash('warning', $translator->trans('support_person.deleted_successfully', [
                    'person_fullname' => $supportPerson->getPerson()->getFullname(),
                    'e' => Grammar::gender($supportPerson->getPerson()->getGender()),
                ], 'app'));
            } catch (\Exception $e) {
                throw $e;
                $this->addFlash('danger', $translator->trans('error_occurred_with_msg',
                    ['msg' => $e->getMessage()], 'app')
                );
            }
        }

        return $this->redirectToRoute('support_edit', ['id' => $supportGroup->getId()]);
    }

    /**
     * @Route("/support-person/{id}/restore", name="support_person_restore", methods="GET")
     * @IsGranted("ROLE_SUPER_ADMIN")
     */
    public function restore(
        int $id,
        SupportPersonRepository $supportPersonRepo,
        SupportRestorer $supportRestorer
    ): Response {
        $supportPerson = $supportPersonRepo->findSupportPerson($id, true);

        $message = $supportRestorer->restore($supportPerson);

        $this->addFlash('success', $message);

        return $this->redirectToRoute('support_show', ['id' => $supportPerson->getSupportGroup()->getId()]);
    }

    /**
     * R??cup??re la liste des personnes rattach??es ?? un suivi.
     *
     * @Route("/support/{id}/people", name="support_people", methods="GET")
     */
    public function getPeopleInSupportGroup(int $id, SupportManager $supportManager, NormalizerInterface $normalizer): JsonResponse
    {
        $supportGroup = $supportManager->getSupportGroup($id);

        $this->denyAccessUnlessGranted('EDIT', $supportGroup);

        return $this->json([
            'action' => 'get_support_people',
            'support_people' => $normalizer->normalize($supportGroup->getSupportPeople(), null, [
                'groups' => ['show_support_person', 'show_person'],
            ]),
        ]);
    }
}
