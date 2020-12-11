<?php

namespace App\Controller\Traits;

use App\Service\Normalisation;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;

trait ErrorMessageTrait
{
    /**
     * Retourne un message d'erreur au format JSON.
     */
    public function getErrorMessage(FormInterface $form = null, Normalisation $normalisation = null): Response
    {
        $msg = [];

        if ($form) {
            foreach ($form->getErrors(true) as $error) {
                if ($normalisation) {
                    $msg[] = $normalisation->unCamelCase($error->getOrigin()->getName()).' => '.$error->getMessage();
                } else {
                    $msg[] = $error->getMessage();
                }
            }
        }

        return $this->json([
            'code' => 200,
            'alert' => 'danger',
            'msg' => 'Une erreur s\'est produite. '.join(' ', $msg),
        ]);
    }

    /**
     * Retourne un message d'accès refusé.
     */
    protected function accessDenied(): Response
    {
        return $this->json([
            'code' => 403,
            'alert' => 'danger',
            'msg' => "Vous n'avez pas les droits pour cette action. Demandez à un administrateur de votre service.",
        ], 200);
    }
}
