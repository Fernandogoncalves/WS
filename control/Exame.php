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
        //verificar como realizar esse lançamento
        if(empty($objExame->data_recebimento))       throw new Exception("Data do Recebimento Exame Não Informada!");
        //verificar como realizar esse lançamento
        if(empty($objExame->usuario_id))   throw new Exception("Endereço Não Informado!");
        
        if(empty($objExame->tipo_exame_id))     throw new Exception("Cidade Não Informada!");
        if(empty($objExame->area_id))         throw new Exception("UF Não Informado!");
      
        if(!Utilidades::validarData($objExame->data_exame))    throw new Exception("Data da Realização do Exame Inválida!");
        if(!Utilidades::validarData($objExame->data_previsao))    throw new Exception("Data da Previsão do Exame Inválida!");
    }    
}