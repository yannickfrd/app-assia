<?php

namespace App\Service;

use ZipArchive;

class DumpDatabase
{
    protected $databaseName;
    protected $userName;
    protected $password;
    protected $host;
    protected $pathMySqlDump;

    public function __construct($databaseName, $userName, $password, $host, $pathMySqlDump)
    {
        $this->databaseName = $databaseName;
        $this->userName = $userName;
        $this->password = $password;
        $this->host = $host;
        $this->pathMySqlDump = $pathMySqlDump;
    }

    public function dump()
    {
        $path = 'public/uploads/backups/'.date('Y/m/d/');
        $fileName = date('Y_m_d_H_i_').'database-backup.sql';

        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }
        // mysqldump --host=db***.hosting-data.io --user=dbu*** --password=*** dbs*** | zip > public/databaseBackup.sql.zip
        $cmd = "mysqldump -h{$this->host} -u{$this->userName} -p{$this->password} {$this->databaseName} > {$path}{$fileName}";

        if ($this->host == 'localhost') {
            $cmd = "{$this->pathMySqlDump}/mysqldump --host={$this->host} --user={$this->userName} {$this->databaseName} > {$path}{$fileName}";
        }

        $output = [];
        exec($cmd, $output, $return);

        $initFile = $path.$fileName;
        $zipFileName = $fileName.'.zip';

        $zip = new \ZipArchive();

        if ($zip->open($path.$zipFileName, ZipArchive::CREATE)) {
            $zip->addFile($initFile, $fileName);
            $zip->close();
        }

        $backupDatas = [
            'return' => $return,
            'size' => filesize($initFile),
            'zipSize' => filesize($path.$zipFileName),
            'fileName' => $zipFileName,
        ];

        if (file_exists($initFile)) {
            unlink($initFile);
        }

        return $backupDatas;
    }

    protected function resultDump($path, $return)
    {
        switch ($return) {
            case 0:
                return 'La base de données <b>'.$this->databaseName.'</b> a été stockée avec succès dans le chemin suivant '.getcwd().'\\'.$path.'</b>';
                break;
            case 1:
                return 'Une erreur s\'est produite lors de la exportation de <b>'.$this->databaseName.'</b> vers'.getcwd().'\\'.$path.'</b>';
                break;
            case 2:
                return 'Une erreur s\'est produite lors de l\'exportation de la base de données';
                break;
        }
    }
}
