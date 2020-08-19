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

    /**
     * Créé une sauvegarde de la base de données.
     */
    public function dump(string $path = null)
    {
        // Optimise la base de données
        $cmd = 'php bin/console doctrine:query:sql "OPTIMIZE TABLE `accommodation`, `accommodation_group`, `accommodation_person`, `avdl`, `contribution`, `database_backup`, `device`, `document`, `evaluation_group`, `evaluation_person`, `eval_adm_person`, `eval_budget_group`, `eval_budget_person`, `eval_family_group`, `eval_family_person`, `eval_housing_group`, `eval_justice_person`, `eval_prof_person`, `eval_social_group`, `eval_social_person`, `export`, `group_people`, `init_eval_group`, `init_eval_person`, `migration_versions`, `note`, `organization`, `origin_request`, `person`, `pole`, `rdv`, `referent`, `role_person`, `service`, `service_device`, `service_organization`, `service_user`, `support_group`, `support_person`, `user`, `user_connection`, `user_device`"';
        exec($cmd);

        if (null == $path) {
            $path = \dirname(__DIR__).'/../public/backups/'.date('Y/m/d/');
        }

        $fileName = date('Y_m_d_H_i_').'database-backup.sql';

        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }

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
