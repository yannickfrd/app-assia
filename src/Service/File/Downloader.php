<?php

namespace App\Service\File;

use Symfony\Component\HttpFoundation\StreamedResponse;

class Downloader
{
    /**
     * Envoie le fichier à télécharger à l'utilisateur.
     */
    public function send(string $file): StreamedResponse
    {
        if (!file_exists($file)) {
            throw new \Exception('This file doesn\'t exist.');
        }

        try {
            $response = new StreamedResponse();

            $response->headers->set('Content-Description', 'File Transfer');
            $response->headers->set('Content-Type', mime_content_type($file) ?? 'application/octet-stream');
            $response->headers->set('Content-Disposition', 'attachment; filename="'.basename($file).'"');
            $response->headers->set('Content-name', basename($file));
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
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
