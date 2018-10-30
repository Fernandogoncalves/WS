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
    
    public function get_listarAreas(){
        // Criando o dao
        $this->objDaoexame = new daoExame();
    
        return $this->objDaoexame->listarAreas();
    }
    
    public function get_listarTiposExames(){
        // Criando o dao
        $this->objDaoexame = new daoExame();
    
        return $this->objDaoexame->listarTiposExames();
    }
      
    public function post_cadastrarExame(){
        $bolRetorno = false;
        // Criando o dao
        $this->objDaoexame = new daoExame();
        // Validando os dados postados
        if(empty($_POST["dadosExame"])) throw new Exception("Dados Não Informados!");
        // Recuperando os dados do paciente
        $objExame = json_decode($_POST["dadosExame"]);  
        $objExame->usuario_id = (int) $_POST["usuario_id"];
        // Validando os dados postados
        $this->validarCadastroExame($objExame);  
        // Cadastrando o exame
        $bolCadastro = $this->objDaoExame->cadastrarExame($objExame);// cadastrando o exame na base
        if(!$bolCadastro) throw new Exception("Não foi possível cadastrar o exame!");
        return true;
    }
    
    /**
     * Método que irá validar os dados de cadastro do Exame
     * 
     * @param Object $objExame
     * @throws Exception
     */
    function validarCadastroExame(stdClass $objExame){
        // Validação dos dados de exame
        if(empty($objExame->data_exame))        throw new Exception("Data da Realização do Exame Não Informada!");
        if(empty($objExame->data_previsao))     throw new Exception("Data da Previsão do Exame Não Informada!");        
        if(empty($objExame->usuario_id))        throw new Exception("Paciente Não Informado!");        
        if(empty($objExame->tipo_exame_id))     throw new Exception("Tipo de exame Não Informado!");
        if(empty($objExame->area_id))           throw new Exception("Área Não Informada!");
      
        if(!Utilidades::validarData($objExame->data_exame))       throw new Exception("Data da Realização do Exame Inválida!");
        if(!Utilidades::validarData($objExame->data_previsao))    throw new Exception("Data da Previsão do Exame Inválida!");
    } 
    
    public function get_previsaoPorTipoExame(){
        // Criando o dao
        $this->objDaoexame = new daoExame();
        // Validando os dados postados
        if(empty($_GET["intIdTipoExame"])) throw new Exception("Tipo do Exame Não Informado!");
        if(empty($_GET["strDataColeta"])) throw new Exception("Data Coleta Não Informada!");
        // Recuperando os dados do paciente
        $intIdTipoExame = (int) $_GET["intIdTipoExame"];
        $strDataColeta  = $_GET["strDataColeta"];
        // Validações
        if($intIdTipoExame == 0) throw new Exception("Usuário Inválido!");
        // Listando os exames do paciente
        $arrPrevisao = $this->objDaoexame->getPrevisaoPorTipoExame($intIdTipoExame, $strDataColeta);
        if(empty($arrPrevisao) || $arrPrevisao["qtd_exames"] == 0) throw new Exception("Não existe uma previsão para esse tipo de exame, favor solicitar ao atendente um prazo e cadastrar manualmente!");
        // Retornando a lista de exames do paciente
        return $arrPrevisao;
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
        $pep =  $_GET["pep"];
        // Validações
        if($intPep == '0') throw new Exception("Pep Inválido!");
        // Filtrando Exames
        $arrExames = $this->objDaoexame->filtrarExames($intIdArea,$intIdTipoExame,$pep);
        if(empty($arrExames)) throw new Exception("Exames não foram Encontrados!");        
        // Retornando a lista de exames filtrados
        return $arrExames;
    }
}
