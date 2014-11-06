<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Ifendirh\SyncBundle\Exec;

use Ifendirh\SyncBundle\Utils\ExternDriveTools;
use PDO;
use RuntimeException;
use Symfony\Component\Process\Process;

/**
 * Description of DatabaseExporter
 *
 * @author tbwa-lamatrix
 */
class DatabaseExporter extends BaseExec {

    protected $user;
    protected $password;
    protected $host;

    public function __construct($output) {
        parent::__construct($output);
        $externDriveTools = new ExternDriveTools();
        $this->databaseDirectoryToExport = $externDriveTools->getDatabasesDirectory();
    }

    public function export($user = "root", $password = '', $host = "localhost", $databases = '') {
        $this->user = $user;
        $this->password = $password;
        $this->host = $host;
        if ($databases == '') {

            $db = new PDO("mysql:host=$host", $user, $password);
            $query = "SHOW DATABASES";
            $exportDatabases = array();
            $exclude = array('information_schema', 'performance_schema');
            foreach ($db->query($query) as $row) {
                if (!in_array($row['Database'], $exclude)) {
                    $exportDatabases[] = $row['Database'];
                }
            }

            $this->exportFile($exportDatabases);
        } else {
            $this->exportFile($databases);
        }
    }

    protected function exportFile($databases) {
        $process = new Process('', null, null, null, null);

        if (is_string($databases)) {
            $process->setCommandLine($this->getQuery($databases));
            $this->getOutput()->writeln("Export : " . $databases);
            $this->runExport($process);
        } elseif (is_array($databases)) {
            foreach ($databases as $database) {
           
                $process->setCommandLine($this->getQuery($database));
                $this->getOutput()->writeln("Export : " . $database. " vers ". $this->databaseDirectoryToExport);
                $this->runExport($process);
            }
        }
    }

    private function getQuery($database) {
        return "mysqldump --databases --add-drop-database " . $database . " -u " . $this->user . " -p" . $this->password . " > " . $this->databaseDirectoryToExport . $database . ".sql";
    }

    private function runExport($process) {
        $process->run(function ($type, $buffer) {
                    if ('err' === $type) {
                        echo 'ERR > ' . $buffer;
                    } else {
                        echo 'OUT > ' . $buffer;
                    }
                });
        if (!$process->isSuccessful()) {
            throw new RuntimeException($process->getErrorOutput());
        }
    }

}
