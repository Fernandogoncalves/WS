<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ModelSituacaoAlunoCurso
 *
 * @author alberto.medeiros
 */
class ModelSituacaoAlunoCurso {
    public $intIdAluno;
    public $intIdCurso;
    public $intParcela;
    public $intPago;
    
    function __construct() {
        
    }
    
    function getIntIdAluno() {
        return $this->intIdAluno;
    }

    function getIntIdCurso() {
        return $this->intIdCurso;
    }

    function getIntParcela() {
        return $this->intParcela;
    }

    function getIntPago() {
        return $this->intPago;
    }

    function setIntIdAluno($intIdAluno) {
        $this->intIdAluno = $intIdAluno;
    }

    function setIntIdCurso($intIdCurso) {
        $this->intIdCurso = $intIdCurso;
    }

    function setIntParcela($intParcela) {
        $this->intParcela = $intParcela;
    }

    function setIntPago($intPago) {
        $this->intPago = $intPago;
    }



}
