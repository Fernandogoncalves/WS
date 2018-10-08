<?php
class ModelCursosAluno {
    public $idCurso;
    public $strCurso;
    public $floValor;
    public $intQtdParcelas;
    
    public $listaSituacaoCurso;
            
    function __construct() {
        
    }
    
    function getListaSituacaoCurso() {
        return $this->listaSituacaoCurso;
    }

    function setListaSituacaoCurso($listaSituacaoCurso) {
        $this->listaSituacaoCurso = $listaSituacaoCurso;
    }

    
    function getIdCurso() {
        return $this->idCurso;
    }

    function getStrCurso() {
        return $this->strCurso;
    }

    function getFloValor() {
        return $this->floValor;
    }

    function getIntQtdParcelas() {
        return $this->intQtdParcelas;
    }

    function setIdCurso($idCurso) {
        $this->idCurso = $idCurso;
    }

    function setStrCurso($strCurso) {
        $this->strCurso = $strCurso;
    }

    function setFloValor($floValor) {
        $this->floValor = $floValor;
    }

    function setIntQtdParcelas($intQtdParcelas) {
        $this->intQtdParcelas = $intQtdParcelas;
    }


}
