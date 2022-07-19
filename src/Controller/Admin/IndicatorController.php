<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Form\Admin\ServiceIndicatorsSearchType;
use App\Form\Model\Admin\ServiceIndicatorsSearch;
use App\Form\Model\Support\SupportSearch;
use App\Form\Support\Support\SupportSearchType;
use App\Repository\Admin\IndicatorRepository;
use App\Repository\Support\SupportPersonRepository;
use App\Service\Indicators\ServiceIndicator;
use App\Service\Indicators\SocialIndicators;
use App\Service\Pagination;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class IndicatorController extends AbstractController
{
    protected $em;
    protected $indicatorRepo;

    public function __construct(EntityManagerInterface $em, IndicatorRepository $indicatorRepo)
    {
        $this->em = $em;
        $this->indicatorRepo = $indicatorRepo;
    }

    /**
     * Indicateurs d'actvitié quotidiens.
     *
     * @Route("/daily_indicators", name="daily_indicators", methods="GET|POST")
     * @IsGranted("ROLE_SUPER_ADMIN")
     */
    public function indicators(Request $request, Pagination $pagination): Response
    {
        return $this->render('app/admin/indicator/daily_indicators.html.twig', [
            'indicators' => $pagination->paginate($this->indicatorRepo->findIndicatorsQuery(), $request, 30),
        ]);
    }

    /**
     * Indicateurs d'actvitié quotidiens.
     *
     * @Route("/indicator/services", name="indicator_services", methods="GET|POST")
     * @IsGranted("ROLE_SUPER_ADMIN")
     */
    public function showServiceIndicators(Request $request, ServiceIndicator $indicators): Response
    {
        $form = $this->createForm(ServiceIndicatorsSearchType::class, $search = new ServiceIndicatorsSearch())
            ->handleRequest($request);

        return $this->render('app/admin/indicator/services_indicators.html.twig', [
            'search' => $search,
            'form' => $form->createView(),
            'servicesIndicators' => $indicators->getServicesIndicators($search),
        ]);
    }

    /**
     * @Route("/indicators/social", name="indicators_social", methods="GET|POST")
     */
    public function showSocialIndicators(Request $request, SupportPersonRepository $supportPersonRepo, SocialIndicators $socialIndicators): Response
    {
        $form = $this->createForm(SupportSearchType::class, $search = new SupportSearch())
            ->handleRequest($request);

        $supports = $supportPersonRepo->findSupportsFullToExport($search);

        return $this->render('app/evaluation/social_indicators.html.twig', [
            'search' => $search,
            'form' => $form->createView(),
            'datas' => $socialIndicators->getResults($supports),
        ]);
    }
}
