<?php

declare(strict_types=1);

namespace App\Controller\Organization;

use App\Entity\Organization\Tag;
use App\Form\Model\Organization\TagSearch;
use App\Form\Organization\Tag\TagSearchType;
use App\Form\Organization\Tag\TagType;
use App\Repository\Organization\TagRepository;
use App\Service\Pagination;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class TagController extends AbstractController
{
    public const NB_ITEMS = 10;

    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @Route("/admin/tags", name="admin_tag_index", methods="GET")
     * @IsGranted("ROLE_ADMIN")
     */
    public function index(Request $request, Pagination $pagination, TagRepository $tagRepo): Response
    {
        $form = $this->createForm(TagSearchType::class, $search = new TagSearch())
            ->handleRequest($request);

        return $this->render('app/organization/tag/tag_index.html.twig', [
            'form' => $form->createView(),
            'tags' => $pagination->paginate(
                $tagRepo->findTagsQuery($search),
                $request,
                self::NB_ITEMS
            ),
        ]);
    }

    /**
     * @Route("/admin/tag/new", name="admin_tag_new", methods="GET|POST")
     * @IsGranted("ROLE_SUPER_ADMIN")
     */
    public function new(Request $request): Response
    {
        $form = $this->createForm(TagType::class, $tag = new Tag())
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($tag);
            $this->em->flush();

            $this->addFlash('success', 'tag.created_successfully');

            return $this->redirectToRoute('admin_tag_index');
        }

        return $this->render('app/organization/tag/tag.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/tag/{id}/edit", name="admin_tag_edit", methods="GET|POST")
     * @IsGranted("ROLE_SUPER_ADMIN")
     */
    public function edit(Tag $tag, Request $request): Response
    {
        $form = $this->createForm(TagType::class, $tag)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();

            $this->addFlash('success', 'tag.updated_successfully');

            return $this->redirectToRoute('admin_tag_index');
        }

        return $this->render('app/organization/tag/tag.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/tag/{id}/delete", name="admin_tag_delete", methods="GET|DELETE")
     * @IsGranted("ROLE_SUPER_ADMIN")
     */
    public function delete(Tag $tag): RedirectResponse
    {
        $this->em->remove($tag);
        $this->em->flush();

        $this->addFlash('warning', 'tag.deleted_successfully');

        return $this->redirectToRoute('admin_tag_index');
    }
}
