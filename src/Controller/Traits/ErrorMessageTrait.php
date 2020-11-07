<?php

namespace App\Controller\Traits;

use App\Service\Normalisation;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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
                    $msg[] = $error->getMessage().' ('.$error->getCause().')';
                }
            }
        }

        return $this->json([
            'code' => 200,
            'alert' => 'danger',
            'msg' => 'Une erreur s\'est produite. '.join(' ', $msg),
        ]);
    }

    // /**
    //  * Retourne une erreur au format Json.
    //  */
    // public function getErrorJson(FormInterface $form = null, string $msg = null): Response
    // {
    //     if ($form) {
    //         $msgArray = [];
    //         foreach ($form->getErrors(true) as $error) {
    //             $msgArray[] = $error->getOrigin()->getName().' => '.$error->getMessage();
    //         }
    //         $msg = join(' ', $msgArray);
    //     }

    //     return $this->json([
    //         'code' => 200,
    //         'action' => 'error',
    //         'alert' => 'danger',
    //         'msg' => 'Une erreur s\'est produite : '.$msg,
    //     ], 200);
    // }

    // /**
    //  * Retourne un message d'erreur au format JSON.
    //  */
    // public function errorMessage(FormInterface $form, ValidatorInterface $validator = null): Response
    // {
    //     $msg = [];
    //     foreach ($validator->validate($form) as $error) {
    //         $msg[] = $error->getMessage();
    //     }

    //     return $this->json([
    //         'code' => 200,
    //         'alert' => 'danger',
    //         'msg' => 'Une erreur s\'est produite : '.join(' ', $msg),
    //     ], 200);
    // }
}
