<?php

namespace App\Controller\Organization;

use App\Entity\Organization\Service;
use App\Entity\Organization\Tag;
use App\Form\Organization\Tag\ServiceTagType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ServiceTagController extends AbstractController
{
    private $manager;

    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Ajout de tags dans un service, à partir du service.
     *
     * @Route("/service/{service}/add-tags", name="service_add_tags", methods="POST")
     */
    public function serviceAddTags(Service $service, Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('EDIT', $service);

        $form = $this->createForm(ServiceTagType::class, $service)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->manager->flush();
        }

        return $this->json([
            'action' => 'add',
            'alert' => 'success',
            'msg' => 'L\'étiquette a bien été ajoutée.',
        ]);
    }

    /**
     * Suppression d'un tag par rapport à un service.
     *
     * @Route("/service/{service}/delete-tag/{tag}", name="service_delete_tag", methods="GET|DELETE")
     */
    public function deleteTagService(Service $service, Tag $tag): JsonResponse
    {
        $this->denyAccessUnlessGranted('EDIT', $service);

        $service->removeTag($tag);
        $this->manager->flush();

        return $this->json([
            'action' => 'delete',
            'alert' => 'success',
            'msg' => 'L\'étiquette a bien été supprimée.',
            'data' => ['tagId' => $tag->getId()],
        ]);
    }
}
