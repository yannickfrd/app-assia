<?php

namespace App\Service;

class DatabaseDumper
{
    protected $databaseName;
    protected $userName;
    protected $password;
    protected $host;

    public function __construct(string $databaseName, string $userName, string $password, string $host)
    {
        $this->databaseName = $databaseName;
        $this->userName = $userName;
        $this->password = $password;
        $this->host = $host;
    }

    /**
     * Crée une sauvegarde de la base de données.
     */
    public function dump(?string $path = null, bool $zipped = true): array
    {
        if (null === $path) {
            $path = '/var/www/backups/'.$this->databaseName.date('/Y/m/');
        }

        $fileName = date('Y-m-d_H_i_').'database-backup.sql'.($zipped ? '.gz' : '');
        $gzipOption = $zipped ? '| gzip' : '';

        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }

        $cmd = "mysqldump --no-tablespaces --host={$this->host} --user={$this->userName} --password={$this->password} {$this->databaseName} {$gzipOption} > {$path}{$fileName}";

        $output = [];
        exec($cmd, $output, $resultCode);

        $file = $path.$fileName;

        return [
            'resultCode' => $resultCode,
            'fileName' => $fileName,
            'path' => $file,
            'size' => filesize($file),
        ];
    }

    protected function resultDump(string $path, bool $resultCode): string
    {
        switch ($resultCode) {
            case 0:
                return 'La base de données <b>'.$this->databaseName.'</b> a été stockée avec succès dans le chemin suivant '.getcwd().'\\'.$path.'</b>';
                break;
            case 1:
                return 'Une erreur s\'est produite lors de la exportation de <b>'.$this->databaseName.'</b> vers'.getcwd().'\\'.$path.'</b>';
                break;
            default:
                return 'Une erreur s\'est produite lors de l\'exportation de la base de données';
                break;
        }
    }
}
