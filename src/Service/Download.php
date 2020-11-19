<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\StreamedResponse;

class Download
{
    /**
     * Envoie le fichier à télécharger à l'utilisateur.
     *
     * @return void
     */
    public function send(string $file): StreamedResponse
    {
        if (file_exists($file)) {
            $response = new StreamedResponse();

            $response->headers->set('Content-Description', 'File Transfer');
            $response->headers->set('Content-Type', 'application/octet-stream');
            $response->headers->set('Content-Disposition', 'attachment; filename="'.basename($file).'"');
            $response->headers->set('Expires', 0);
            $response->headers->set('Pragma', 'public');
            $response->headers->set('Content-Length', filesize($file));
            $response->setPrivate();
            $response->headers->addCacheControlDirective('no-cache', true);
            $response->headers->addCacheControlDirective('must-revalidate', true);
            $response->setCallback(function () use ($file) {
                readfile($file);
            });

            return $response->send();
        }

        return null;
    }
}
