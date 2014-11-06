<?php

namespace Ifendirh\SyncBundle\Exec;

use Symfony\Component\Process\Process;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Synchronize file 
 */
class Synchronizer extends BaseExec{

    private $fromDirectory;
    private $toDirectory;
    private $rsyncString;
    private $exclude;

    function __construct($output) {
        parent::__construct($output);

        $this->rsyncString = 'rsync ';
        $this->exclude = ' --exclude-from=excludes/sync_var.txt ';
    }

    public function exec() {
        return $this->rsync($this->fromDirectory, $this->toDirectory);
    }

    public function rsync($fromDirectory = '', $toDirectory = '', $options = '-vrltD') {

        //add a space after the options if there is not
        if (substr($options, -1) != ' ') {
            $options .= ' ';
        }

        $msg = '';
        if ($fromDirectory == '' || $toDirectory == '') {
            $msg = 'Impossible de lancer rsync vers les répertoires spécifiés : [' . $fromDirectory . '] > [' . $toDirectory . ']';
        } else {
            $msgValidateDirectories = $this->validateDirectory(array($fromDirectory, $toDirectory));
            if ($msgValidateDirectories == '') {

                $exec = $this->rsyncString . $options . $this->exclude . $fromDirectory . ' ' . $toDirectory;

                $this->getOutput()->writeln('<comment>Executer : ' . $exec . '</comment>');
                $process = new Process($exec,null,null,null,null);
                $process->run(function ($type, $buffer) {
                    if ('err' === $type) {
                        echo 'ERR > ' . $buffer;
                    } else {
                        echo 'OUT > ' . $buffer;
                    }
                });
                if (!$process->isSuccessful()) {
                    throw new \RuntimeException($process->getErrorOutput());
                }

                $msg = $process->getOutput();
            } else {
                $msg = '<error>' . $msgValidateDirectories . '<error>';
            }
        }

        $this->getOutput()->writeln($msg);
    }

    /**
     * valid directories
     * 
     * @param type $directories string|array
     */
    private function validateDirectory($directories) {
        $msg = '';
        if (is_string($directories) && !is_dir($directories)) {
            $msg .= $directories . " n'est pas un répertoire valide.\n\r";
        } elseif (is_array($directories)) {
            foreach ($directories as $directory) {
                if (!is_dir($directory)) {
                    $msg .= $directory . " n'est pas un répertoire valide.\n\r";
                }
            }
        }
        return $msg;
    }

}
