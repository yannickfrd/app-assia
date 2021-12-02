<?php

namespace App\Controller\Admin;

use App\Service\Pagination;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\Model\Support\SupportSearch;
use App\Controller\Traits\ErrorMessageTrait;
use App\Service\Indicators\ServiceIndicator;
use App\Service\Indicators\SocialIndicators;
use App\Repository\Admin\IndicatorRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Form\Admin\ServiceIndicatorsSearchType;
use App\Form\Model\Admin\ServiceIndicatorsSearch;
use App\Form\Support\Support\SupportSearchType;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\Support\SupportPersonRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class IndicatorController extends AbstractController
{
    use ErrorMessageTrait;

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
        return $this->render('app/admin/indicator/dailyIndicators.html.twig', [
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

        return $this->render('app/admin/indicator/servicesIndicators.html.twig', [
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

        return $this->render('app/evaluation/socialIndicators.html.twig', [
            'search' => $search,
            'form' => $form->createView(),
            'datas' => $socialIndicators->getResults($supports),
        ]);
    }
}
