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
        // Realizando o login e senha
        $arrUsuario =  $this->objDaoUsuario->enviarSenha($arrDados);
       
        // Caso a senha tenha sido alterada
        if(!empty($arrUsuario)){
            $this->enviarEmailSenha($arrUsuario);
            $bolRetorno = true;
        }
        return $bolRetorno;
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
    
    
    
}