<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once(__DIR__ . '../../control/Empreendimento.php');
require_once(__DIR__ . '/dao.php');

/**
 * Description of daoEmpreendimento
 *
 * @author Alberto Medeiros
 */
class daoEmpreendimento extends Dao {


    function __construct() {
        parent::__construct();
    }

    
    /**
     * 
     * @param type $arrDados
     * @param type $bolExport
     */
    function getContatosPorOrigem($arrDados, $bolExport = false) {
        
        $strSQL = "SELECT "
                . " *"
                . "FROM " . $arrDados["origem"] . " o ";
        
        $strNID = "";
        // Caso o email tenha sido preenchido
        if (!empty($arrDados["nid_emp"])) {
            $strSQL .= ' INNER JOIN node n ON n.nid = ' . $arrDados["nid_emp"];
            $strNID = ", o.nid_emp";
        }
        // FILTRO DE REGIONAL
        $arrOrigem = array("webform_views_cadastrese_e_garanta_condies_especiais", "webform_views_ligamos_para_voc", "webform_views_atendimento_por_email");
        // CASO SEJA INFORMADO A REGIONAL E 
        if (!empty($arrDados["regional"]) && in_array($_GET["origem"], $arrOrigem)) {
            $strSQL .= ' INNER JOIN field_data_field_estado estado ON o.nid_emp = estado.entity_id AND estado.field_estado_tid = ' . $arrDados["regional"];
        }
        // cOMEÇANDO O WHERE
        $strSQL .= " WHERE 1 = 1 ";
        
        
        // Caso o email tenha sido preenchido
        if (!empty($arrDados["regional"]) && !in_array($_GET["origem"], $arrOrigem))
            $strSQL .= " AND o.regional = " . (int) $arrDados["regional"];
        
        // Caso o email tenha sido preenchido
        if (!empty($arrDados["nid_emp"])) {
            $strSQL .= " AND o.nid_emp = ". $arrDados["nid_emp"];
        }

        // Caso o nome tenha sido preenchido
        if (!empty($arrDados["nome"]))
            $strSQL .= " AND o.nome LIKE '%" . $arrDados["nome"] . "%' ";

        // Caso o email tenha sido preenchido
        if (!empty($arrDados["email"]))
            $strSQL .= " AND  o.email = '". $arrDados["email"] . "'";

        // Caso o email tenha sido preenchido
        if (!empty($arrDados["datainicio"]))
            $strSQL .= " AND  submitted BETWEEN '" . $arrDados["datainicio"] . "' AND '" . $arrDados["datafim"] . "'";

        try {
            
            $strSQL .= " GROUP BY o.email, o.telefone {$strNID}";
            
            $this->sql = $strSQL;
            $this->prepare();
            $this->executar();
            return $this->buscarDoResultadoAssoc();
        } catch (Exception $e) {
            $this->adicionarErro("Error!: " . $e->getMessage());
            echo '{"erro":"' . $e->getMessage() . '"}';
        }
    }
    /**
     * Irá retornar a noticia
     * @param type $nid
     * @return stdClass
     */
    function node_load($nid) {
        try {
            // Cast no valor para garantir a integridade
            $nid = (int) $nid;
            $this->sql = "SELECT 
                            title
                        FROM node 
                        WHERE nid = {$nid}";
            $this->prepare();
            $this->executar();
            $row = $this->buscarDoResultadoAssoc();
            $arrObjSituacao = array();
            foreach ($row as $key => $value) {
                $objNoticia = new stdClass();
                $objNoticia->title = $value['title'];
                //setando os valores
                return $objNoticia;
            }
        } catch (Exception $ex) {
        }
    }
    /**
     * Irá retornar a taxonomia
     * 
     * @param type $tid
     * @return \stdClass
     */
    function taxonomy_term_load($tid) {
        try {
            // Cast no valor para garantir a integridade
            $tid = (int) $tid;
            $this->sql = "SELECT * FROM farmacia.taxonomy_term_data t where tid = {$tid}";
            $this->prepare();
            $this->executar();
            $row = $this->buscarDoResultadoAssoc();
            $arrObjSituacao = array();
            foreach ($row as $key => $value) {
                $objNoticia = new stdClass();
                $objNoticia->name = $value['name'];
                //setando os valores
                return $objNoticia;
            }
        } catch (Exception $ex) {
            
        }
    }

}
