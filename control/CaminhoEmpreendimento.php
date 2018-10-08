<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Empreendimento
 *
 * @author Alberto Medeiros
 */
class CaminhoEmpreendimento {
    //put your code here
    
    private $daoCaminhoEmpreendimento;
    private $daoEmpreendimento;
    
    public function get_caminhos(){
        // Criando o dao
        $this->daoCaminhoEmpreendimento = new daoCaminhoEmpreendimento();
        $this->daoEmpreendimento = new daoEmpreendimento();
        
        if(!isset($_GET["codigo"])){
            $arrDados = array();
            $arrDados["primeira_interacao"] = $this->formataDados($this->daoCaminhoEmpreendimento->getPrimeiraInteracao(array()));
            $arrDados["segunda_interacao"] = $this->formataDados($this->daoCaminhoEmpreendimento->getSegundaInteracao(array()));
            $arrDados["terceira_interacao"] = $this->formataDados($this->daoCaminhoEmpreendimento->getTerceiraInteracao(array()));
            $arrDados["quarta_interacao"] = $this->formataDados($this->daoCaminhoEmpreendimento->getQuartaInteracao(array()));
        }else{
            $arrFiltro["codigo"] = (int) @$_GET["codigo"];
            $arrDados = $this->daoCaminhoEmpreendimento->getCaminhosEmpreendimento($arrFiltro);
            $arrDados = $this->formataDados($arrDados);
        }
        
        return $arrDados;
    }
    
    
    function formataDados($arrRetorno){

        $arrRetornoDados = array();
        // Reansformando os objetos em array
        foreach($arrRetorno as $intChave => $objDados){
            $arrRetorno[$intChave] = (array) $objDados;
        }

        foreach($arrRetorno[0] as $strChave => $strDados){
            $arrRetornoDados["header"][] = $this->getChaveFormatada($strChave);
        }
//         $arrRetornoDados["header"] = array("Codigo do empreendimento", "");
//         echo "<pre>";
//         var_dump($arrRetornoDados["header"]);die;
        
        $rows = array();
        $intContador = 0;
        foreach($arrRetorno as $strChave => $strDados){

            $rows[$intContador] = array();
            foreach($strDados as $strChave => $strValor){

                $rows[$intContador][$strChave] = $this->getValorPorChave($strChave, $strValor);
            }

            $intContador++;
        }

        $arrRetornoDados["rows"] = $rows;

        return $arrRetornoDados;
    }

    function getValorPorChave($strChave, $strValor){

        switch ($strChave) {
            case "sid":
                        $strValor = $strValor;
                break;
            case "uid":
                        $strValor = ($strValor == 0) ? "Anônimo" : $strValor;
                break;
            case "nome":
                        $strValor = $strValor;
                break;
            case "telefone":
                        $strValor = $strValor;
                break;
            case "email":
                        $strValor = $strValor;
                break;
            case "mensagem":
                        $strValor = $strValor;
                break;
            case "nid_emp":
//                 echo "<pre>";
//                 var_dump((!empty($strValor) && $strValor != null));die;
//                     echo $strValor . "<br />";
                        $strValor = (!empty($strValor) && $strValor != null) ? $this->node_load($strValor)->title : "Não foi possível encontrar o empreendimento";
                break;
            case "campanha_id":
                        $strValor = (!empty($strValor)  && $strValor != null) ? $this->node_load($strValor)->title : "Não foi possível encontrar a campanha";
                break;
            case "submitted":
                        $strValor = date("d/m/Y H:i", strtotime($strValor));
                break;
            case "remote_addr":
                        $strValor = $strValor;
                break;
            case "title":
                        $strValor = $strValor;
                break;
            case "regional":
                        $strValor = (!empty($strValor)  && $strValor != null) ? $this->taxonomy_term_load(preg_replace("/[^0-9]/", "", $strValor))->name  : "Não foi possível encontrar o regional";
                break;
            case "setor_novo":
                        $strValor = (!empty($strValor)  && $strValor != null) ? $this->taxonomy_term_load(preg_replace("/[^0-9]/", "", $strValor))->name  : "Não foi possível encontrar o setor";
                break;
            case "tipo":
                        $strValor = (!empty($strValor) && $strValor != null) ? $this->taxonomy_term_load(preg_replace("/[^0-9]/", "", $strValor))->name  : "Não foi possível encontrar o setor";
                break;
            default:
                $strValor = $strValor;
                break;

        }

        return $strValor;
    }


    function getChaveFormatada($strChave){

        switch ($strChave) {
            case "path":
                    $strChave = "Endereco";
                break;
            case "hits":
                        $strChave = "Total de acessos";
                break;
            case "uf":
                        $strChave = "Sigla estado";
                break;
            case "empreendimento":
                        $strChave = "Nome do empreendimento no sistema";
                break;
            case "email":
                        $strChave = "E-mail";
                break;
            case "mensagem":
                        $strChave = "Mensagem";
                break;
            case "nid_emp":
                        $strChave = "Empreendimento";
                break;
            case "title":
                        $strChave = "Pagina ou Empreendimento";
                break;
            case "url":
                        $strChave = "Origem do acesso interno ou externo";
                break;
            case "total_time":
                        $strChave = "Total de tempo carregando a p�gina";
                break;
            case "remote_addr":
                        $strChave = "Endereço IP";
                break;

        }

        return $strChave;
    }
    
    /**
     * Irá retornar a noticia
     * 
     * @param type $nid
     * @return type
     */
    function  node_load($nid){
        // Criando o dao
        $this->daoEmpreendimento = new daoEmpreendimento();
        return $this->daoEmpreendimento->node_load($nid);
    }
    /**
     * Irá retornar a noticia
     * 
     * @param type $nid
     * @return type
     */
    function  taxonomy_term_load($nid){
        // Criando o dao
        $this->daoEmpreendimento = new daoEmpreendimento();
        return $this->daoEmpreendimento->taxonomy_term_load($nid);
    }
}
