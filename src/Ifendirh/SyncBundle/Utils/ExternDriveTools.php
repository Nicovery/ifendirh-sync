<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Ifendirh\SyncBundle\Utils;

use Symfony\Component\Process\Process;

/**
 * Description of Tools
 *
 * @author tbwa-lamatrix
 */
class ExternDriveTools {

    private $workDriveName = 'Work';

    public function getWWWDirectory() {
        $user = $this->getUser();
        
        $wwwDirectory = '/media/' . $user . '/' . $this->workDriveName . '/www/';
        if (is_dir($wwwDirectory)) {
            return $wwwDirectory;
        } else {
            return '';
        }
    }

    public function getDatabasesDirectory() {
        $user = $this->getUser();
        $databaseDirectory = '/media/' . $user . '/' . $this->workDriveName . '/databases/';
        if (is_dir($databaseDirectory)) {
            return $databaseDirectory;
        } else {
            return '';
        }
    }

    private function getUser() {
        $process = new Process('whoami');
        $process->run();
        if (!$process->isSuccessful()) {
            throw new \RuntimeException($process->getErrorOutput());
        }

        $user = $process->getOutput();
        $user = str_replace(array("\n", "\r", "\t"), '', $user);
        return $user;
    }

}
