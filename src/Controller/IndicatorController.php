<?php

namespace App\Controller;

use App\Service\Pagination;
use App\Form\Model\SupportGroupSearch;
use App\Repository\IndicatorRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\SupportPersonRepository;
use App\Controller\Traits\ErrorMessageTrait;
use App\Form\Support\SupportGroupSearchType;
use App\Service\Indicators\SocialIndicators;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class IndicatorController extends AbstractController
{
    use ErrorMessageTrait;

    protected $manager;
    protected $repo;

    public function __construct(EntityManagerInterface $manager, IndicatorRepository $repo)
    {
        $this->manager = $manager;
        $this->repo = $repo;
    }

    /**
     * Indicateurs d'actvitié quotidiens.
     *
     * @Route("daily_indicators", name="daily_indicators", methods="GET|POST")
     * @IsGranted("ROLE_SUPER_ADMIN")
     */
    public function indicators(Request $request, Pagination $pagination): Response
    {
        return $this->render('app/indicator/dailyIndicators.html.twig', [
            'indicators' => $pagination->paginate($this->repo->findIndicatorsQuery(), $request, 30),
        ]);
    }

    /**
     * @Route("/indicators/social", name="indicators_social", methods="GET|POST")
     */
    public function showSocialIndicators(Request $request, SupportPersonRepository $repoSupportPerson, SocialIndicators $socialIndicators): Response
    {
        $search = new SupportGroupSearch();

        $form = ($this->createForm(SupportGroupSearchType::class, $search))
            ->handleRequest($request);

        $supports = $repoSupportPerson->findSupportsFullToExport($search);

        return $this->render('app/evaluation/socialIndicators.html.twig', [
            'supportGroupSearch' => $search,
            'form' => $form->createView(),
            'datas' => $socialIndicators->getResults($supports),
        ]);
    }
}
