<?php

namespace App\Controller;

use App\Controller\Traits\ErrorMessageTrait;
use App\Repository\IndicatorRepository;
use App\Service\Pagination;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
}
