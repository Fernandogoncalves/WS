<?php

/**
 * Controlador Usuário
 *
 * @author Régis Perez
 */
class Exame {
    
    /**
     * Irá conter o objeto  daoUsuário
     *
     * @var daoExame
     */
    private $objDaoExame;
    
      
    /**
     *
     * @throws Exception
     * @return boolean
     */
    public function post_validarCadastro(){
        // Criando o dao
        $this->objDaoExame = new daoExame();
        // Validando os dados postados
        if(empty($_POST["strCPF"])) throw new Exception("CPF Não Informado!");
        if(empty($_POST["strEmail"])) throw new Exception("E-mail Não Informado!");
        // Criando os parametros para validar 
        $arrDados = array();
        $arrDados["strCPF"]     = preg_replace("/[^0-9]/", "", $_POST["strCPF"]);
        $arrDados["strEmail"]   = $_POST["strEmail"];
        // Realizando o login e senha
        if($this->objDaoUsuario->existeCPF($arrDados))      throw new Exception("CPF já cadastrado!");
        if($this->objDaoUsuario->existeEmail($arrDados))    throw new Exception("E-mail já cadastrado!");
        return true;
    }
    
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
        $objPaciente->perfil_id = 1;// Setando o id perfil para paciente
        $objPaciente->cpf = preg_replace("/[^0-9]/", "", $objPaciente->cpf);// Removendo formatação do cpf
        // Cadastrando o paciente
        $bolCadastro = $this->objDaoExame->cadastrarExame($objExame);// cadastrando o paciente na base
        if(!$bolCadastro) throw new Exception("Não foi possível cadastrar o usuário!");
        // Recuperando todos os usuários admin
        $arrIDsOnesinal = $this->objDaoUsuario->getIdsOnesignalPorPefil(2);
        $arrIds = array();
        // Formatando os ids para envio em massa
        foreach($arrIDsOnesinal as $arrValor){
            $arrIds[] = $arrValor["codigo_onesignal"];
        }
        // Criando os dados de notificação
        $arrDadosNotificacao = array(
            'include_player_ids' => $arrIds,
            "headings" => array("en" => "Cadastro de Paciente Pendente"),
            'contents' => array("en" => "Paciente com o nº do PEP: " . $objPaciente->pep)
        );
        // Enviando a notificação
        $objRerotno = Utilidades::enviarNotificacao($arrDadosNotificacao);
        return true;
    }
    
    /
    
    /**
     * Método que irá validar os dados de cadastro do Paciente
     * 
     * @param Object $objPaciente
     * @throws Exception
     */
    function validarCadastoPaciente(stdClass $objPaciente){
        // Validação dos dados de usuário
        if(empty($objPaciente->sexo))       throw new Exception("Sexo Não Informado!");
        if(empty($objPaciente->endereco))   throw new Exception("Endereço Não Informado!");
        if(empty($objPaciente->cidade))     throw new Exception("Cidade Não Informada!");
        if(empty($objPaciente->uf))         throw new Exception("UF Não Informado!");
        if(empty($objPaciente->contato))    throw new Exception("Nº de Contato Não Informado!");
        if(empty($objPaciente->pep))        throw new Exception("Nº PEP Não Informado!");
        if(!Utilidades::validarData($objPaciente->data_nascimento))    throw new Exception("Data Nascimento Inválida!");
        // validando os dados que são comuns ao paciente e a equipe médica
        $this->validarCadasto($objPaciente);
    }
    
    
}