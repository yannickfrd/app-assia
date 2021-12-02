<?php

use EasyCorp\Bundle\EasyDeployBundle\Configuration\DefaultConfiguration;
use EasyCorp\Bundle\EasyDeployBundle\Deployer\DefaultDeployer;

return new class() extends DefaultDeployer {
    public function configure(): DefaultConfiguration
    {
        return $this->getConfigBuilder()
            // SSH connection string to connect to the remote server (format: user@host-or-IP:port-number)
            ->server('')
            // the absolute path of the remote server directory where the project is deployed
            ->deployDir('')
            // the URL of the Git repository where the project code is hosted
            ->repositoryUrl('github.com/RomMad/app-assia.git')
            // the repository branch to deploy
            ->repositoryBranch('main');
    }

    // run some local or remote commands before the deployment is started
    public function beforeStartingDeploy(): void
    {
        // $this->runLocal('./vendor/bin/simple-phpunit');
    }

    // run some local or remote commands after the deployment is finished
    public function beforeFinishingDeploy(): void
    {
        // $this->runRemote('{{ console_bin }} app:my-task-name');
        // $this->runLocal('say "The deployment has finished."');
    }
};
