<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once(__DIR__ . '../../control/Contato.php');
require_once(__DIR__ . '/dao.php');

/**
 * Description of daoEmpreendimento
 *
 * @author Alberto Medeiros
 */
class daoContato extends Dao {


    function __construct() {
        parent::__construct();
    }

    
    /**
     * 
     * @param type $arrDados
     * @param type $bolExport
     */
    function getContatosPorOrigem($arrDados, $bolExport = false) {
        
        $strSQL = "SELECT
                      count(nid) total,
                      title
                    FROM
                       ".$arrDados["origem"]." w
                    INNER JOIN node n on
                      w.nid_emp = nid
                    group by title
                    ORDER BY total DESC";
        try {
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

    /**
     * Irá retornar a taxonomia
     *
     * @param type $tid
     * @return \stdClass
     */
    function variable_get($strVariavel) {
        $strSql = "SELECT * FROM variable v WHERE name = '{$strVariavel}'";
        try {
            // Cast no valor para garantir a integridade
//             $tid = (int) $tid;
            $this->sql = $strSql;
            $this->prepare();
            $this->executar();
            $row = $this->buscarDoResultadoAssoc();
            return unserialize($row[0]["value"]);
        } catch (Exception $ex) {
            echo $ex->getMessage();die;
        }
    }
}
