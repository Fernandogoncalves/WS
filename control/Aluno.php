<?php

require_once(__DIR__ . '/../model/ModelAlunos.php');
require_once(__DIR__ . '/../model/ModelCursosAluno.php');
require_once(__DIR__ . '/../model/ModelFoneAluno.php');
require_once(__DIR__ . '/../model/ModelSituacaoAlunoCurso.php');
class Aluno {

    private $daoAluno;
    private $modelAluno;
    
    function get_listarAlunos() {
        $this->daoAluno = new DaoAluno();
        return $this->daoAluno->listarAlunos();
        
    }
    function get_aluno() {
        $intIdAluno = (int) $_GET["intIdAluno"];
        $this->daoAluno = new DaoAluno();
        return $this->daoAluno->getAluno($intIdAluno);
        
    }
    function get_cursoAluno() {
        $intIdAluno = (int) $_GET["intIdAluno"];
        $this->daoAluno = new DaoAluno();
        return $this->daoAluno->getCursoAluno($intIdAluno);
        
    }
    
    
    function post_novoRelease($request){
        
        $this->releaseDao = new DaoRelease();
        $this->modelRelease = new ModelRelease();
        
        $this->modelRelease->setId_sistema($request->idSistema);
        $this->modelRelease->setId_cliente($request->idCliente);
        $this->modelRelease->setTipo($request->tipo);
        $this->modelRelease->setBranch($request->branch);
        $this->modelRelease->setDat_ent_fab($request->datEntFab);
        $this->modelRelease->setDat_in_hom($request->datInHom);
        $this->modelRelease->setDat_pla_exp($request->datPlaExp);
        $this->modelRelease->setDat_exp($request->datExp);
        $this->modelRelease->setObservacao($request->observacao);

        return $this->releaseDao->novoRelease($this->modelRelease);
    }

}
?>