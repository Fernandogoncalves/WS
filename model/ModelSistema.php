<?php
class ModelSistema {
    public $codSistema;
    public $tagSistema;
    public $nomeSistema;

    function getCodSistema() {
        return $this->codSistema;
    }

    function getTagSistema() {
        return $this->tagSistema;
    }

    function getNomeSistema() {
        return $this->nomeSistema;
    }

    function setCodSistema($codSistema) {
        $this->codSistema = $codSistema;
    }

    function setTagSistema($tagSistema) {
        $this->tagSistema = $tagSistema;
    }

    function setNomeSistema($nomeSistema) {
        $this->nomeSistema = $nomeSistema;
    }

}