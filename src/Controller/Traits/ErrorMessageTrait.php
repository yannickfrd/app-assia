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
            'alert' => 'danger',
            'msg' => 'Une erreur s\'est produite. '.join(' ', $msg),
        ]);
    }
}
