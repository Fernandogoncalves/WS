<?php
class ModelAlunos {
    
    public $intTd;
    public $strNome;
    public $strEmail;
    public $strData_nascimento;
    public $strLogradoudo;
    public $strCep;
    public $strUf;
    public $intAtivo;
    
    public $listaCursoAluno;
    public $listaFonesAluno;
    
    function getListaCursoAluno() {
        return $this->listaCursoAluno;
    }

    function getListaFonesAluno() {
        return $this->listaFonesAluno;
    }

    function setListaCursoAluno($listaCursoAluno) {
        $this->listaCursoAluno = $listaCursoAluno;
    }

    function setListaFonesAluno($listaFonesAluno) {
        $this->listaFonesAluno = $listaFonesAluno;
    }

        
    function __construct() {
    }

    function getIntTd() {
        return $this->intTd;
    }

    function getStrNome() {
        return $this->strNome;
    }

    function getStrEmail() {
        return $this->strEmail;
    }

    function getStrData_nascimento() {
        return $this->strData_nascimento;
    }

    function getStrLogradoudo() {
        return $this->strLogradoudo;
    }

    function getStrCep() {
        return $this->strCep;
    }

    function getStrUf() {
        return $this->strUf;
    }

    function getIntAtivo() {
        return $this->intAtivo;
    }

    function setIntTd($intTd) {
        $this->intTd = $intTd;
    }

    function setStrNome($strNome) {
        $this->strNome = $strNome;
    }

    function setStrEmail($strEmail) {
        $this->strEmail = $strEmail;
    }

    function setStrData_nascimento($strData_nascimento) {
        $this->strData_nascimento = $strData_nascimento;
    }

    function setStrLogradoudo($strLogradoudo) {
        $this->strLogradoudo = $strLogradoudo;
    }

    function setStrCep($strCep) {
        $this->strCep = $strCep;
    }

    function setStrUf($strUf) {
        $this->strUf = $strUf;
    }

    function setIntAtivo($intAtivo) {
        $this->intAtivo = $intAtivo;
    }


}   
