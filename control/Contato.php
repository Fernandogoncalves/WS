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
class Contato {
    //put your code here
    
    private $daoContatos;
    
    public function get_filtros(){
        // Criando o dao
        $this->daoContatos = new daoContato();
        
        if(isset($_GET["origem"])){
            $arrFiltro = array();
            $arrFiltro["origem"]    = @$_GET["origem"];
            $bolEsport = (@$_GET["export"] == "true") ? true : false;
            $arrRetorn = $this->daoContatos->getContatosPorOrigem($arrFiltro, $bolEsport);
            $arrRetorn = $this->formataDados($arrRetorn);
        }else{
            $views = $this->daoContatos->variable_get('webform_mysql_views_views');
            $arrRetorn = array();
            $arrRetorn[] = "Pra selecionar um formul�rio basta fornecer o paramatro ?origem=formul�rio";
            foreach($views as $intChave => $arrValor){
                $arrRetorn[] = strtoupper(trim(preg_replace("/webform_views_|\_/", " ", $arrValor))) . " origem=" . $arrValor;
            }
        }
        return $arrRetorn;
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

        }

        return $strValor;
    }


    function getChaveFormatada($strChave){

        switch ($strChave) {
            case "sid":
                        $strChave = "ID do contato";
                break;
            case "uid":
                        $strChave = "ID do Usuário";
                break;
            case "nome":
                        $strChave = "Usuário Contato";
                break;
            case "telefone":
                        $strChave = "Telefone";
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
                        $strChave = "title";
                break;
            case "campanha_id":
                        $strChave = "Campanha";
                break;
            case "submitted":
                        $strChave = "Data Contato";
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
