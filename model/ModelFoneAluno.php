<?php
/**
 * Description of FoneAluno
 *
 * @author alberto.medeiros
 */
class ModelFoneAluno {
    public $intId;
    public $strFone;
    public $intTipo;
            
    function __construct() {
        
    }
    function getIntId() {
        return $this->intId;
    }

    function getStrFone() {
        return $this->strFone;
    }

    function getIntTipo() {
        return $this->intTipo;
    }

    function setIntId($intId) {
        $this->intId = $intId;
    }

    function setStrFone($strFone) {
        $this->strFone = $strFone;
    }

    function setIntTipo($intTipo) {
        $this->intTipo = $intTipo;
    }


}
