<?php

namespace App\Controller\Support;

use App\Service\SupportGroup\SupportManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class SupportPersonController extends AbstractController
{
    /**
     * Récupère la liste des personnes rattachées à un suivi.
     *
     * @Route("/support/{id}/people", name="support_people", methods="GET")
     */
    public function getPeopleInSupportGroup(int $id, SupportManager $supportManager, NormalizerInterface $normalizer): JsonResponse
    {
        $supportGroup = $supportManager->getSupportGroup($id);

        $this->denyAccessUnlessGranted('EDIT', $supportGroup);

        return $this->json([
            'action' => 'getSupportPeople',
            'supportPeople' => $normalizer->normalize($supportGroup->getSupportPeople(), null, [
                'groups' => ['show_support_person', 'show_person'],
            ]),
        ]);
    }
}
