<?php

/* * ****************************************************************************
  Nome do Arquivo   : daoMonitoraPedidos.php
  Descrição         : Classe especialista de manipulação de dados
  Programador       : José Gabriel
  CRC               : 49682
  Data              : 19/01/2015
  Diretório         : ./dao/
  Alteração  : Nome - Data - numero crc
  # Descrição das alteração...
 * **************************************************************************** */

require_once(__DIR__ . '../../control/Aluno.php');
require_once(__DIR__ . '/dao.php');

class DaoAluno extends Dao {

    private $objAluno;

    function __construct() {
        parent::__construct();
        $this->modelRelease = new ModelAlunos();
    }

    /**
     * Listando todos os alunos
     * @return \ModelAlunos
     */
    function listarAlunos() {
        try {
            $this->sql = "SELECT 
                            id,
                            nome,
                            email,
                            data_nascimento,
                            Cep,
                            UF
                        FROM aluno 
                        WHERE ativo = 1";
            $this->prepare();
            $this->executar();
            $row = $this->buscarDoResultadoAssoc();
            $arrObjAluno = array();
            $listaResult = array();
            foreach ($row as $key => $value) {
                $objAluno = new ModelAlunos();
                //setando os valores
                $objAluno->setIntTd($value['id']);
                $objAluno->setStrNome($value["nome"]);
                $objAluno->setStrEmail($value["email"]);
                $objAluno->setStrData_nascimento($value["data_nascimento"]);
                $objAluno->setStrCep($value['Cep']);
                $objAluno->setStrUf($value['UF']);
                $arrObjAluno[] = $objAluno;
            }
            if (!empty($arrObjAluno)) {
                return $arrObjAluno;
            } 
            
        } catch (Exception $e) {
            $this->adicionarErro("Error!: " . $e->getMessage());
            echo '{"erro":"' . $e->getMessage() . '"}';
        }
    }
    
    /**
     * Listando todos os alunos
     * @return \ModelAlunos
     */
    function getAluno($intIdAluno) {
        try {
            $intIdAluno = (INT) $intIdAluno;
            $this->sql = "SELECT 
                            *
                        FROM aluno 
                        WHERE ativo = 1
                        and id = {$intIdAluno}";
            $this->prepare();
            $this->executar();
            $row = $this->buscarDoResultadoAssoc();
            $arrObjAluno = array();
            $listaResult = array();
            foreach ($row as $key => $value) {
                $objAluno = new ModelAlunos();
                //setando os valores
                $objAluno->setIntTd($value['id']);
                $objAluno->setStrNome($value["nome"]);
                $objAluno->setStrEmail($value["email"]);
                $objAluno->setStrData_nascimento($value["data_nascimento"]);
                $objAluno->setStrCep($value['Cep']);
                $objAluno->setStrUf($value['UF']);
                $objAluno->setStrLogradoudo($value['Logradoudo']);
                $objAluno->setListaCursoAluno($this->getCursoAluno($intIdAluno));
                $objAluno->setListaFonesAluno($this->getFonesAluno($intIdAluno));
                $arrObjAluno[] = $objAluno;
            }
            if (!empty($arrObjAluno)) {
                return $arrObjAluno;
            } 
            
        } catch (Exception $e) {
        }
    }
    /**
     * Irá Realizar a busca dos cursos do aluno
     * 
     * @param type $intIdAluno
     * @return ModelCursosAluno
     */
    function getCursoAluno($intIdAluno) {
        try {
            // Cast no valor para garantir a integridade
            $intIdAluno = (int) $intIdAluno;
            $this->sql = "SELECT
                                curso.id,
                                curso.nome,
                                valor,
                                parcelas
                        FROM aluno
                        INNER JOIN curso_aluno ON
                                id_aluno = aluno.id
                        INNER JOIN 	curso ON
                                curso.id = id_curso
                        WHERE	aluno.id = {$intIdAluno}";

            $this->prepare();
            $this->executar();

            //return $this->buscarDoResultadoAssoc();
            $row = $this->buscarDoResultadoAssoc();
            $arrObjAlunoCurso = array();
            foreach ($row as $key => $value) {
                $objCursosAluno = new ModelCursosAluno();
                //setando os valores
                $objCursosAluno->setIdCurso($value['id']);
                $objCursosAluno->setStrCurso($value["nome"]);
                $objCursosAluno->setIntQtdParcelas($value["parcelas"]);
                $objCursosAluno->setFloValor($value["valor"]);
                $objCursosAluno->setListaSituacaoCurso($this->getSituacaoCursoAluno($value['id'], $intIdAluno));
                $arrObjAlunoCurso[] = $objCursosAluno;
            }

            if (!empty($arrObjAlunoCurso)) {
                return $arrObjAlunoCurso;
            } else {

                $this->adicionarErro(" Não há Crusos para o aluno ");
            }
        } catch (Exception $ex) {
            
        }
    }
    
    /**
     * Irá Realizar a busca Da situação do aluno no curso
     * 
     * @param integer $intCurso
     * @param integer $intIdAluno
     * @return ModelCursosAluno
     */
    function getSituacaoCursoAluno($intCurso, $intIdAluno) {
        try {
            // Cast no valor para garantir a integridade
            $intIdAluno = (int) $intIdAluno;
            $this->sql = "SELECT 
                            id_curso, 
                            id_aluno, 
                            parcela, 
                            pago 
                        FROM pamento 
                        WHERE id_curso = {$intCurso} AND id_aluno = {$intIdAluno}";

            $this->prepare();
            $this->executar();

            //return $this->buscarDoResultadoAssoc();
            $row = $this->buscarDoResultadoAssoc();
            $arrObjSituacao = array();
            foreach ($row as $key => $value) {
                $objSituacao = new ModelSituacaoAlunoCurso();
                //setando os valores
                $objSituacao->setIntIdCurso($value['id_curso']);
                $objSituacao->setIntIdAluno($value["id_aluno"]);
                $objSituacao->setIntParcela($value["parcela"]);
                $objSituacao->setIntPago($value["pago"]);
                $arrObjSituacao[] = $objSituacao;
            }

            if (!empty($arrObjSituacao)) {
                return $arrObjSituacao;
            } else {

                $this->adicionarErro(" Não há Crusos para o aluno ");
            }
        } catch (Exception $ex) {
            
        }
    }
    
    /**
     * Irá Realizar a busca dos fones do aluno
     * 
     * @param type $intIdAluno
     * @return ModelFoneAluno
     */
    function getFonesAluno($intIdAluno) {
        try {
            // Cast no valor para garantir a integridade
            $intIdAluno = (int) $intIdAluno;
            $this->sql = "SELECT * FROM telefone WHERE id_aluno = {$intIdAluno}";

            $this->prepare();
            $this->executar();

            //return $this->buscarDoResultadoAssoc();
            $row = $this->buscarDoResultadoAssoc();
            $arrObjModel = array();
            foreach ($row as $key => $value) {
                $objModelFoneAluno = new ModelFoneAluno();
                //setando os valores
                $objModelFoneAluno->setIntId($value['id_aluno']);
                $objModelFoneAluno->setStrFone($value["Fone"]);
                $objModelFoneAluno->setIntTipo($value["tipo_fone"]);
                $arrObjModel[] = $objModelFoneAluno;
            }
            if (!empty($arrObjModel)) {
                return $arrObjModel;
            } else {
                $this->adicionarErro(" Não há Crusos para o aluno ");
            }
        } catch (Exception $ex) {
            
        }
    }

}
