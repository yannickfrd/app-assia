<?php

namespace App\Service;

use ZipArchive;
use Spatie\DbDumper\Databases\MySql;

class DumpDatabase
{
    protected $download;

    protected $databaseName;
    protected $userName;
    protected $password;
    protected $host;
    protected $pathMySqlDump;

    public function __construct(Download $download, $databaseName, $userName, $password, $host, $pathMySqlDump)
    {
        $this->download = $download;

        $this->databaseName = $databaseName;
        $this->userName = $userName;
        $this->password = $password;
        $this->host = $host;
        $this->pathMySqlDump = $pathMySqlDump;
    }

    public function dump($export = false)
    {
        $backupDatas = [];

        // C:\wamp64\bin\mariadb\mariadb10.4.10\bin\mysqldump --host=localhost --user=root  esperer95_app > public/databaseBackup.sql
        // mysqldump --host=db***.hosting-data.io --user=dbu*** --password=*** dbs*** | zip > public/databaseBackup.sql.zip
        $path = 'backups/'.date('Y/m/');

        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }

        $fileName = date('Y_m_d_H_i_').'database-backup.sql';
        // $mime = 'application/zip';

        $cmd = "mysqldump -h{$this->host} -u{$this->userName} -p{$this->password} {$this->databaseName} > {$path}{$fileName}";

        if ($this->host == 'localhost') {
            $cmd = "{$this->pathMySqlDump}/mysqldump --host={$this->host} --user={$this->userName} {$this->databaseName} > {$path}{$fileName}";
        }

        $output = [];
        exec($cmd, $output, $return);

        $initfile = $path.$fileName;
        $zipFile = $fileName.'.zip';

        $zip = new \ZipArchive();

        if ($zip->open($path.$zipFile, ZipArchive::CREATE)) {
            $zip->addFile($initfile, $fileName);
            $zip->close();
        }

        $backupDatas['size'] = filesize($initfile);
        $backupDatas['zipSize'] = filesize($path.$zipFile);
        $backupDatas['fileName'] = $zipFile;

        if (file_exists($initfile)) {
            unlink($initfile);
        }

        if ($export == true && file_exists($zipFile)) {
            return $this->download->send($zipFile);
        }

        return $backupDatas;
        // switch ($return) {
        //     case 0:
        //     echo 'La base de données <b>'.$this->databaseName.'</b> a été stockée avec succès dans le chemin suivant '.getcwd().'\\'.$path.'</b>';
        //     break;
        //     case 1:
        //     echo 'Une erreur s\'est produite lors de la exportation de <b>'.$this->databaseName.'</b> vers'.getcwd().'\\'.$path.'</b>';
        //     break;
        //     case 2:
        //     echo 'Une erreur s\'est produite lors de l\'exportation de la base de données';
        //     break;
        // }

        // MySql::create()
        //     ->setDumpBinaryPath($pathMySqlDump)
        //     ->setDbName($this->databaseName)
        //     ->setUserName($this->userName)
        //     ->setPassword($this->password)
        //     ->dumpToFile($fileName);
    }
}
