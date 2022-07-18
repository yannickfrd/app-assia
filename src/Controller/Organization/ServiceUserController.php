<?php

declare(strict_types=1);

namespace App\Controller\Organization;

use App\Entity\Organization\ServiceUser;
use App\Entity\Organization\User;
use App\Form\Admin\Security\SecurityUserServicesType;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/service-user")
 */
final class ServiceUserController extends AbstractController
{
    private $em;
    private $translator;

    public function __construct(EntityManagerInterface $em, TranslatorInterface $translator)
    {
        $this->em = $em;
        $this->translator = $translator;
    }

    /**
     * @Route("/{id}/add", name="service_user_add", methods="POST")
     * @IsGranted("ROLE_ADMIN")
     */
    public function add(User $user, Request $request): RedirectResponse
    {
        $form = $this->createForm(SecurityUserServicesType::class)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var ServiceUser $serviceUser */
            foreach ($form->get('serviceUser')->getData() as $serviceUser) {
                $service = $serviceUser->getService();

                if (!$user->hasService($service)) {
                    $this->denyAccessUnlessGranted('EDIT', $service);
                    $user->addServiceUser($serviceUser);

                    $this->addFlash('success', $this->translator->trans('service.added_successfully',
                        ['service_name' => $service->getName()], 'app')
                    );
                }
            }

            $this->em->flush();
        }

        return $this->redirectToRoute('security_user', ['id' => $user]);
    }

    /**
     * @Route("/{id}/toggle-main", name="service_user_toggle_main", methods="GET")
     */
    public function toggleMain(ServiceUser $serviceUser): JsonResponse
    {
        $this->denyAccessUnlessGranted('EDIT', $serviceUser->getService());

        $serviceUser->toggleMain();

        $this->em->flush();

        return $this->json([
            'action' => 'update',
            'alert' => 'success',
            'msg' => $this->translator->trans('service.toogle_main', [
                'service_name' => $serviceUser->getService()->getName(),
                'is' => $serviceUser->getMain() ? ' est' : ' n\'est plus',
                'user' => $serviceUser->getUser()->getFirstname(),
            ], 'app'),
        ]);
    }

    /**
     * @Route("/{id}/delete/{_token}", name="service_user_delete", methods="DELETE")
     */
    public function delete(ServiceUser $serviceUser, string $_token): JsonResponse
    {
        $service = $serviceUser->getService();

        $this->denyAccessUnlessGranted('EDIT', $service);

        if (!$this->isCsrfTokenValid('delete'.$serviceUser->getId(), $_token)) {
            return $this->json([
                'alert' => 'danger',
                'msg' => $this->translator->trans('error_occurred', [], 'app'),
            ]);
        }

        $this->em->remove($serviceUser);
        $this->em->flush();

        return $this->json([
            'action' => 'delete',
            'alert' => 'success',
            'msg' => $this->translator->trans('service.removed_successfully',
                ['service_name' => $serviceUser->getService()->getName()], 'app'
            ),
            'service' => ['id' => $service->getId()],
        ]);
    }
}
