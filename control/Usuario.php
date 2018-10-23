<?php

/**
 * Controlador Usuário
 *
 * @author Alberto Medeiros
 */
class Usuario {
    
    /**
     * Irá conter o objeto  daoUsuário
     *
     * @var daoUsuario
     */
    private $objDaoUsuario;
    
    
    public function get_usuarioPorId(){
        return $this->getResposta();
    }
    
    public function get_listaCancer(){
        // Criando o dao
        $this->objDaoUsuario = new daoUsuario();
        
        return $this->objDaoUsuario->listaCancer();
    }
    
    public function post_login(){
        // Criando o dao
        $this->objDaoUsuario = new daoUsuario();
        // Validando os dados postados        
        if(empty($_POST["strLogin"])) throw new Exception("Login ou Senha Não Informado");
        if(empty($_POST["strSenha"])) throw new Exception("Login ou Senha Não Informado");
        // Criando os parametros para login
        $arrDados = array();
        $arrDados["strLogin"] = preg_replace("/[^0-9]/", "", $_POST["strLogin"]);
        $arrDados["strSenha"] = $_POST["strSenha"];
        // Realizando o login e senha
        $arrRetorno = $this->objDaoUsuario->loginUsuario($arrDados);
        // Caso o usuário não seja encontrado
        if(empty($arrRetorno)) throw new Exception("Usuário não encontrado!");
        // retornando o usuário
        return $arrRetorno;
    }
    
    public function post_esqueciSenha(){
        $bolRetorno = false;
        // Criando o dao
        $this->objDaoUsuario = new daoUsuario();
        // Validando os dados postados
        if(empty($_POST["strEmail"])) throw new Exception("E-mail Não Informado");
        // Criando os parametros para login
        $arrDados = array();
        $arrDados["strEmail"] = $_POST["strEmail"];
        // Alterando a senha do usuário caso encontre
        $arrUsuario =  $this->objDaoUsuario->enviarSenha($arrDados);
        // Caso a senha tenha sido alterada
        if(!empty($arrUsuario)){
            $this->enviarEmailSenha($arrUsuario);
            $bolRetorno = true;
        }
        return $bolRetorno;
    }
    
    public function post_existeEmail(){
        $bolRetorno = false;
        // Criando o dao
        $this->objDaoUsuario = new daoUsuario();
        // Validando os dados postados
        if(empty($_POST["strEmail"])) throw new Exception("E-mail Não Informado!");
        // Criando os parametros para login
        $arrDados = array();
        $arrDados["strEmail"] = $_POST["strEmail"];
        // Realizando o login e senha
        return $this->objDaoUsuario->existeEmail($arrDados);
    }
    
    /**
     * 
     * @throws Exception
     * @return boolean
     */
    public function post_existeCpf(){
        // Criando o dao
        $this->objDaoUsuario = new daoUsuario();
        // Validando os dados postados
        if(empty($_POST["strCPF"])) throw new Exception("CPF Não Informado!");
        // Criando os parametros para login
        $arrDados = array();
        $arrDados["strCPF"] = $_POST["strCPF"];
        // Realizando o login e senha
        return $this->objDaoUsuario->existeCPF($arrDados);
    }
    
    /**
     *
     * @throws Exception
     * @return boolean
     */
    public function post_validarCadastro(){
        // Criando o dao
        $this->objDaoUsuario = new daoUsuario();
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
    
    public function post_cadastrarPaciente(){
        $bolRetorno = false;
        // Criando o dao
        $this->objDaoUsuario = new daoUsuario();
        // Validando os dados postados
        if(empty($_POST["dadosPaciente"])) throw new Exception("Dados Não Informados!");
        // Recuperando os dados do paciente
        $objPaciente = json_decode($_POST["dadosPaciente"]);
        $objPaciente->onesignal = $_POST["onesignal"];// Recuperando o id do onesignal
        // Validando os dados postados
        $this->validarCadastoPaciente($objPaciente);
        $objPaciente->perfil_id = 1;// Setando o id perfil para paciente
        $objPaciente->cpf = preg_replace("/[^0-9]/", "", $objPaciente->cpf);// Removendo formatação do cpf
        // Cadastrando o paciente
        $bolCadastro = $this->objDaoUsuario->cadastrarUsuario($objPaciente);// cadastrando o paciente na base
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
    
    /**
     * Enviará o email para o usuário
     * @param array $arrUsuario
     */
    public function enviarEmailSenha(array $arrUsuario){
        // Criando o email
        $mail = new PHPMailer();
        $mail->IsSMTP();		// Ativar SMTP
        //$mail->SMTPDebug = 3;		// Debugar: 1 = erros e mensagens, 2 = mensagens apenas
        $mail->SMTPAuth = true;		// Autenticação ativada
        $mail->SMTPSecure = 'ssl';	// SSL REQUERIDO pelo GMail
        $mail->Host = 'smtp.gmail.com';	// SMTP utilizado
        $mail->Port = 465;  		// A porta 587 deverá estar aberta em seu servidor
        $mail->SMTPSecure = 'ssl';
        $mail->Username = 'conexaovidaimip@gmail.com';
        $mail->Password = 'conexaovida';
        $mail->FromName = 'Conexão Vida - IMIP';
        $mail->IsHTML(true);
        // Caso seja um único email
        $mail->addAddress($arrUsuario["email"]);
        // Add a recipient
        $mail->WordWrap = 50;                                 // Set word wrap to 50 characters
        $mail->Subject = "Recuperar Senha";
        // Criando o corpo do email
        $strBody = "Olá, " . $arrUsuario["nome"] . "! <br />";
        $strBody .= "Conforme solicitado, segue nova senha: <br />";
        $strBody .= "<b>Senha:</b> " . $arrUsuario["novaSenha"] . " <br />";
        // Colocando o corpo do email        
        $mail->Body = $strBody;
        if(!$mail->send()) throw new Exception("Não foi possível enviar e-mail!");
    }
    
    /**
     * Método que irá validar os dados de cadastro do Paciente
     * 
     * @param Object $objPaciente
     * @throws Exception
     */
    function validarCadastroPaciente(stdClass $objPaciente){
        // Validação dos dados de usuário
        if(empty($objPaciente->sexo))       throw new Exception("Sexo Não Informado!");
        if(empty($objPaciente->endereco))   throw new Exception("Endereço Não Informado!");
        if(empty($objPaciente->cidade))     throw new Exception("Cidade Não Informada!");
        if(empty($objPaciente->uf))         throw new Exception("UF Não Informado!");
        if(empty($objPaciente->contato))    throw new Exception("Nº de Contato Não Informado!");
        if(empty($objPaciente->pep))        throw new Exception("Nº PEP Não Informado!");
        if(!Utilidades::validarData($objPaciente->data_nascimento))    throw new Exception("Data Nascimento Inválida!");
        // validando os dados que são comuns ao paciente e a equipe médica
        $this->validarCadastro($objPaciente);
    }
    
    /**
     * Método que irá validar os dados de acesso do usuário seja paciente ou equipe médica
     * 
     * @param Object $objPaciente
     * @throws Exception
     */
    function validarCadastro(stdClass $objPaciente){
        // Validação dos dados comuns
        if(empty($objPaciente)) throw new Exception("Dados Não Informados!");
        if(empty($objPaciente->cpf)) throw new Exception("CPF Não Informado!");
        if(!Utilidades::validaCPF($objPaciente->cpf)) throw new Exception("CPF invalido!");
        if(empty($objPaciente->senha)) throw new Exception("Senha Não Informada!");
        if($objPaciente->senha != $objPaciente->confirmacao_senha) throw new Exception("Senhas são diferentes!");
        if(empty($objPaciente->email)) throw new Exception("E-mail Não Informado!");
        if(empty($objPaciente->nome)) throw new Exception("Nome Não Informado!");
    }
    
}