<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once(__DIR__ . '../../control/Visitas.php');
require_once(__DIR__ . '/dao.php');
require_once(__DIR__ . '/daoEmpreendimento.php');

/**
 * Description of daoEmpreendimento
 *
 * @author Alberto Medeiros
 */
class daoVisitas extends Dao {


    function __construct() {
        parent::__construct();
    }

    
    /**
     * 
     * @param type $arrDados
     * @param type $bolExport
     */
    function getVisitas($arrDados) {
        
        $strSQL = "SELECT
                      nid codigo_empreendimento,
                      n.title empreendimento,
                      name uf,
                      tid codigo_estado,
                      COUNT(path) hits
                FROM node n
                inner join field_data_field_estado f on
                  f.entity_id = n.nid
                INNER JOIN taxonomy_term_data  t on
                  f.field_estado_tid = t.tid
                INNER JOIN accesslog a ON
                  CONCAT('node/', nid)   =  a.path
                WHERE 1 = 1";
        
        // Caso o email tenha sido preenchido
        if (!empty($arrDados["codigo"]))
            $strSQL .= " AND  n.nid = ". $arrDados["codigo"];

        // Caso o email tenha sido preenchido
        if (!empty($arrDados["uf"]))
            $strSQL .= " AND  name = '" . $arrDados["uf"] . "'";

        try {
            
            $strSQL .= " GROUP BY
                  codigo_empreendimento,
                  empreendimento,
                  uf,
                  codigo_estado
                ORDER BY hits desc";
//             echo $strSQL;die;
            $this->sql = $strSQL;
            $this->prepare();
            $this->executar();
//             echo "<pre>aaa";
//             var_dump($this);die;
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
