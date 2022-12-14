<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Form\Admin\ImportType;
use App\Form\Model\Admin\Import;
use App\Service\Import\ImportDatasHebergement;
use App\Service\Import\ImportHudaData;
use App\Service\Import\ImportPAFDatas;
use App\Service\Import\ImportPlaceDatas;
use App\Service\Import\ImportUserDatas;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/import")
 * @IsGranted("ROLE_SUPER_ADMIN")
 */
final class ImportController extends AbstractController
{
    protected $import;
    protected $translator;

    public function __construct(Import $import, TranslatorInterface $translator)
    {
        $this->import = $import;
        $this->translator = $translator;
    }

    /**
     * Import de données du pôle Hébergement.
     *
     * @Route("/heb", name="import_heb", methods="GET|POST")
     */
    public function imporHeb(Request $request, ImportDatasHebergement $importDatas): Response
    {
        return $this->import($request, $importDatas, 'import_heb.csv');
    }

    /**
     * Import de données de l'HUDA.
     *
     * @Route("/huda", name="import_huda", methods="GET|POST")
     */
    public function importHuda(Request $request, ImportHudaData $importDatas): Response
    {
        return $this->import($request, $importDatas, 'import_huda.csv');
    }

    /**
     * Import des hôtels.
     *
     * @Route("/places", name="import_places", methods="GET|POST")
     */
    public function importPlaces(Request $request, ImportPlaceDatas $importDatas): Response
    {
        return $this->import($request, $importDatas, 'import_places.csv');
    }

    /**
     * Import des utilisateurs.
     *
     * @Route("/users", name="import_users", methods="GET|POST")
     */
    public function importUser(Request $request, ImportUserDatas $importDatas): Response
    {
        return $this->import($request, $importDatas, 'import_users.csv');
    }

    /**
     * Import des PAFs.
     *
     * @Route("/paf", name="import_paf", methods="GET|POST")
     */
    public function importPAF(Request $request, ImportPAFDatas $importDatas): Response
    {
        return $this->import($request, $importDatas, 'import_paf.csv');
    }

    /**
     * Importe les données.
     */
    private function import(Request $request, object $importDatas, string $filename): Response
    {
        $file = \dirname(__DIR__).'/../../public/import/datas/'.$filename;

        $datas = $importDatas->getDatas($file);

        $form = $this->createForm(ImportType::class, $this->import)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $nbItems = count($importDatas->importInDatabase($file, $this->import->getServices(), $request));
            if ($nbItems > 0) {
                (new FilesystemAdapter($_SERVER['DB_DATABASE_NAME']))->clear();

                $this->addFlash('success', $this->translator->trans('admin.import.is_successful',
                    ['nb_items' => $nbItems], 'app')
                );

                return $this->redirectToRoute('home');
            } else {
                $this->addFlash('warning', 'admin.import.no_result');
            }
            // } else {
            // dump($datas);
        }

        return $this->renderForm('app/admin/import.html.twig', [
            'form' => $form,
            'datas' => $datas,
        ]);
    }
}
