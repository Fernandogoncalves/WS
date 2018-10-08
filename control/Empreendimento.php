<?php

/**
 * Description of Empreendimento
 *
 * @author Alberto Medeiros
 */
class Empreendimento {
    //put your code here
    
    private $daoEmpreendimento;
    
    public function get_filtroFormulario(){
        
        // Criando o dao
        $this->daoEmpreendimento = new daoEmpreendimento();
        
        if(isset($_GET["origem"])){
            
            $arrFiltro = array();
            $arrFiltro["origem"]    = @$_GET["origem"];
    
            // Caso o nome tenha sido preenchido
            if(!empty($_GET["nome"]))
                $arrFiltro["nome"]      = @$_GET["nome"];
    
            // Caso o email tenha sido preenchido
            if(!empty($_GET["email"]))
                $arrFiltro["email"]      = @$_GET["email"];
    
            // Caso o email tenha sido preenchido
            if(!empty($_GET["empreendimento"])){
                $arrFiltro["nid_emp"]      =(int) @$_GET["empreendimento"];
            }
    
            // Caso o email tenha sido preenchido
            if(!empty($_GET["datainicio"])){
                $arrFiltro["datainicio"]   = (string)  @$_GET["datainicio"] . " 00:00:00";
                $arrFiltro["datafim"]      = (string) (!empty($_GET["datafim"])) ? @$_GET["datafim"] . " 23:59:59" : date("Y-m-d") . " 23:59:59" ;
            }
    
            $arrOrigem = array("webform_views_cadastrese_e_garanta_condies_especiais", "webform_views_ligamos_para_voc", "webform_views_atendimento_por_email");
            // Caso o email tenha sido preenchido
            if(!empty($_GET["estado"]) && !in_array($_GET["origem"], $arrOrigem))
                $arrFiltro["regional"]      = (is_numeric(@$_GET["estado"])) ? (int) "tid_" . @$_GET["estado"] : @$_GET["estado"];
            else if(!empty($_GET["estado"]))
                $arrFiltro["regional"]      =  (int) @$_GET["estado"];
    
            $bolEsport = (@$_GET["export"] == "true") ? true : false;
            $arrRetorn = $this->daoEmpreendimento->getContatosPorOrigem($arrFiltro, $bolEsport);
            
            if($arrRetorn)
                $arrRetorn = $this->formataDados($arrRetorn);
        
        }else{
            // Criando o dao
            $this->daoContatos = new daoContato();
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
                        $intTimestamp = strtotime($strValor);
                        
                        $strValor = date("d/m/Y H:i", $intTimestamp);
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
