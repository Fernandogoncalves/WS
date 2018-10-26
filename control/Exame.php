<?php

/**
 * Controlador Exame
 *
 * @author Régis Perez
 */
class Exame {
    
    /**
     * Irá conter o objeto  daoExame
     *
     * @var daoExame
     */
    private $objDaoExame;
    
      
    public function post_cadastrarExame(){
        $bolRetorno = false;
        // Criando o dao
        $this->objDaoexame = new daoExame();
        // Validando os dados postados
        if(empty($_POST["dadosExame"])) throw new Exception("Dados Não Informados!");
        // Recuperando os dados do paciente
        $objExame = json_decode($_POST["dadosExame"]);        
        // Validando os dados postados
        $this->validarCadastroExame($objExame);        
        // Cadastrando o exame
        $bolCadastro = $this->objDaoExame->cadastrarExame($objExame);// cadastrando o exame na base
        if(!$bolCadastro) throw new Exception("Não foi possível cadastrar o exame!");
        return true;
    }
    
    /
    
    /**
     * Método que irá validar os dados de cadastro do Exame
     * 
     * @param Object $objExame
     * @throws Exception
     */
    function validarCadastroExame(stdClass $objExame){
        // Validação dos dados de exame
        if(empty($objExame->data_exame))       throw new Exception("Data da Realização do Exame Não Informada!");
        if(empty($objExame->data_previsao))       throw new Exception("Data da Previsão do Exame Não Informada!");        
        if(empty($objExame->usuario_id))   throw new Exception("Paciente Não Informado!");        
        if(empty($objExame->tipo_exame_id))     throw new Exception("Cidade Não Informada!");
        if(empty($objExame->area_id))         throw new Exception("UF Não Informado!");
      
        if(!Utilidades::validarData($objExame->data_exame))    throw new Exception("Data da Realização do Exame Inválida!");
        if(!Utilidades::validarData($objExame->data_previsao))    throw new Exception("Data da Previsão do Exame Inválida!");
    } 
    
    /**
     * Método que irá retornar os exames pelo id do paciente (usuário)
     * @throws Exception
     * @return mixed
     */
    public function get_listarExamesDoUsuarioPorId(){
        // Criando o dao
        $this->objDaoexame = new daoExame();
        // Validando os dados postados
        if(empty($_GET["intIdUsuario"])) throw new Exception("Id Não Informado!");
        // Recuperando os dados do paciente
        $intIdUsuario = (int) $_GET["intIdUsuario"];
        // Validações
        if($intIdUsuario == 0) throw new Exception("Usuário Inválido!");
        // Listando os exames do paciente
        $arrExames = $this->objDaoexame->listarExamesDoPaciente($intIdUsuario);
        if(empty($arrExames)) throw new Exception("Exames não foram Encontrados!");        
        // Retornando a lista de exames do paciente
        return $arrExames;
    }

    public function get_filtrarExames(){
        // Criando o dao
        $this->objDaoexame = new daoExame();
        
        // Recuperando os filtros 
        $intIdArea = (int) $_GET["intIdArea"];
        $intIdTipoExame = (int) $_GET["intIdTipoExame"];
        $intPep = (int) $_GET["intPep"];
        // Validações
        if($intPep == 0) throw new Exception("Pep Inválido!");
        // Filtrando Exames
        $arrExames = $this->objDaoexame->filtrarExames($intIdArea,$intIdTipoExame,$intPep);
        if(empty($arrExames)) throw new Exception("Exames não foram Encontrados!");        
        // Retornando a lista de exames filtrados
        return $arrExames;
    }
}
