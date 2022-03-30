<?php

namespace App\Service\Evaluation;

use App\Entity\Evaluation\EvaluationGroup;
use App\Entity\Support\Note;
use App\Entity\Support\SupportGroup;
use App\Service\ExportPDF;
use App\Service\ExportWord;
use App\Service\SupportGroup\SupportCollections;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Twig\Environment;

class EvaluationExporter
{
    public const TITLE = 'Grille d\'évaluation sociale';

    private $supportCollections;
    private $renderer;

    public function __construct(SupportCollections $supportCollections, Environment $renderer)
    {
        $this->supportCollections = $supportCollections;
        $this->renderer = $renderer;
    }

    /**
     * @return StreamedResponse|null
     */
    public function export(SupportGroup $supportGroup, Request $request): ?Response
    {
        $evaluation = $this->getEvaluation($supportGroup);

        if (!$evaluation) {
            return null;
        }

        $exportType = $request->attributes->get('type');
        $export = 'pdf' === $exportType ? new ExportPDF() : new ExportWord(true);
        $logoPath = $supportGroup->getService()->getPole()->getLogoPath();
        $pathImage = 'pdf' === $exportType ? $export->getPathImage($logoPath) : null;
        $fullnameSupport = $supportGroup->getHeader()->getFullname();

        $payments = $this->supportCollections->getAllPayments($supportGroup);
        $content = $this->getContent($supportGroup, $evaluation, $exportType, $pathImage, $fullnameSupport, $payments);

        $export->createDocument($content, self::TITLE, $logoPath, $fullnameSupport);

        return $export->download();
    }

    public function createNote(SupportGroup $supportGroup): ?Note
    {
        $evaluation = $this->getEvaluation($supportGroup);

        if (!$evaluation) {
            return null;
        }

        $payments = $this->supportCollections->getAllPayments($supportGroup);
        $content = $this->getContent($supportGroup, $evaluation, 'note', null, null, $payments);

        return (new Note())
            ->setTitle(self::TITLE.' '.(new \DateTime())->format('d/m/Y'))
            ->setContent($content)
            ->setType(Note::TYPE_NOTE)
            ->setSupportGroup($supportGroup);
    }

    private function getContent(
        SupportGroup $supportGroup,
        EvaluationGroup $evaluation,
        string $exportType,
        string $pathImage = null,
        string $fullnameSupport = null,
        array $payments = []
    ): string {
        $organization = $supportGroup->getService()->getPole()->getOrganization()->getName();

        return $this->renderer->render('app/evaluation/export/evaluation_export.html.twig', [
            'type' => $exportType,
            'support' => $supportGroup,
            'referents' => $this->supportCollections->getReferents($supportGroup),
            'evaluation' => $evaluation,
            'payments' => $payments,
            'lastRdv' => $this->supportCollections->getLastRdvs($supportGroup),
            'nextRdv' => $this->supportCollections->getNextRdvs($supportGroup),
            'title' => self::TITLE,
            'logo_path' => $pathImage,
            'header_info' => $organization.' | '.self::TITLE.' | '.$fullnameSupport,
        ]);
    }

    private function getEvaluation(SupportGroup $supportGroup): ?EvaluationGroup
    {
        return $this->supportCollections->getEvaluation($supportGroup);
    }
}
