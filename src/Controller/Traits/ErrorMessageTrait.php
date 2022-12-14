<?php

declare(strict_types=1);

namespace App\Controller\Traits;

use App\Service\Normalisation;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

trait ErrorMessageTrait
{
    /**
     * Retourne un message d'erreur au format JSON.
     */
    public function getErrorMessage(
        FormInterface $form = null,
        Normalisation $normalisation = null,
        array $translationFiles = ['forms']
    ): JsonResponse {
        $msg = [];

        if ($form) {
            foreach ($form->getErrors(true) as $error) {
                $msg[] = ($normalisation ? $normalisation->unCamelCase($error->getOrigin()->getName(), ' ', $translationFiles)
                    : $error->getOrigin()->getName()).' : '.$error->getMessage().'<br/>';
            }
        }

        return $this->json([
            'alert' => 'danger',
            'msg' => 'Une erreur s\'est produite. <br/>'.join(' ', $msg),
        ]);
    }
}
