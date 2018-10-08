<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once(__DIR__ . '../../control/CaminhoEmpreendimento.php');
require_once(__DIR__ . '/dao.php');
require_once(__DIR__ . '/daoEmpreendimento.php');

/**
 * Description of daoEmpreendimento
 *
 * @author Alberto Medeiros
 */
class daoCaminhoEmpreendimento extends Dao {


    function __construct() {
        parent::__construct();
    }
    
    /**
     *
     * @param type $arrDados
     * @param type $bolExport
     */
    function getCaminhosEmpreendimento($arrDados) {
    
        $intCodigo = (int) $arrDados["codigo"];
        $strSQL = "SELECT
                      title,
                      a.path AS path,
                      COUNT(path) AS hits,
                      url
                    FROM accesslog a
                    WHERE path = 'node/{$intCodigo}'
                    GROUP BY path, url,title
                    ORDER BY hits DESC
                    LIMIT 30";
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
     * 
     * @param type $arrDados
     * @param type $bolExport
     */
    function getPrimeiraInteracao($arrDados) {
        
        $strSQL = "SELECT
                      a.path AS path,
                      COUNT(path) AS hits,
                      MAX(title) AS title,
                      AVG(timer) AS average_time,
                      SUM(timer) AS total_time
                    FROM accesslog a
                    WHERE path = 'home'
                    GROUP BY path
                    ORDER BY hits DESC";
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
     *
     * @param type $arrDados
     * @param type $bolExport
     */
    function getSegundaInteracao($arrDados) {
    
        $strSQL = "SELECT
                      a.path AS path,
                      COUNT(path) AS hits,
                      MAX(title) AS title,
                      AVG(timer) AS average_time,
                      SUM(timer) AS total_time
                    FROM accesslog a
                    INNER JOIN taxonomy_term_data on
                      CONCAT('taxonomy/term/', tid)   =  path
                    WHERE tid in (SELECT tid FROM taxonomy_term_data where vid = 3)
                    GROUP BY path
                    ORDER BY hits DESC";
    
    
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
     *
     * @param type $arrDados
     * @param type $bolExport
     */
    function getTerceiraInteracao($arrDados) {
    
        $strSQL = "SELECT
                      a.path AS path,
                      COUNT(path) AS hits,
                      MAX(title) AS title,
                      AVG(timer) AS average_time,
                      SUM(timer) AS total_time
                    FROM accesslog a
                    WHERE path like '%empreendimentos/%'
                    GROUP BY path
                    ORDER BY hits DESC
                    limit 10";
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
    
    function getQuartaInteracao($arrDados) {
    
        $strSQL = "SELECT
                  a.path AS path,
                  COUNT(path) AS hits,
                  MAX(title) AS title,
                  AVG(timer) AS average_time,
                  SUM(timer) AS total_time,
                  name uf
                FROM accesslog a
                LEFT JOIN field_data_field_estado f on
                  CONCAT('node/', f.entity_id)   =  path
                LEFT JOIN taxonomy_term_data  t on
                  f.field_estado_tid = t.tid
                WHERE path like '%node/%'
                GROUP BY path, name
                ORDER BY hits DESC
                limit 40";
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

}
