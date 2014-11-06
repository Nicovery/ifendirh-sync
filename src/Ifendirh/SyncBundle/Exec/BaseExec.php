<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Ifendirh\SyncBundle\Exec;

/**
 * Description of BaseExec
 *
 * @author tbwa-lamatrix
 */
class BaseExec {

    protected $output;

    public function __construct($output) {
        $this->output = $output;
    }

    public function getOutput() {
        return $this->output;
    }

}
