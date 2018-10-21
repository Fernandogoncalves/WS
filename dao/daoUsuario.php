<?php
/**
 * Dao Padrão dos usuários
 */

require_once(__DIR__ . '../../control/Usuario.php');
require_once(__DIR__ . '/dao.php');

/**
 * Description of daoEmpreendimento
 *
 * @author Alberto Medeiros
 */
class daoUsuario extends Dao {


    function __construct() {
        parent::__construct();
    }

    /**
     * Método de login do usuário
     * 
     * @param array $arrDados
     * @return array
     */
    function loginUsuario(array $arrDados){
        try {
            // Cast no valor para garantir a integridade
            $strLogin = $arrDados["strLogin"];
            $strSenha = md5($arrDados["strSenha"]);
            $this->sql ="SELECT
                          u.id,
                          u.perfil_id,
                          u.nome,
                          u.numero_pep,
                          u.contato,
                          u.sexo,
                          u.email,
                          u.login,
                          u.cancer_id,
                          u.ultimo_acesso
                        FROM usuario u 
                        WHERE
                          u.login = :login
                          and 
                          u.senha = :senha";
            $this->prepare();
            $this->bind("login", $strLogin);
            $this->bind("senha", $strSenha);
            $this->executar();
            $arrRetorno = $this->buscarDoResultadoAssoc(true);
            if(!empty($arrRetorno)){
                $arrRetorno["strCpf"] = $arrRetorno["login"];
            }
            // Retornando os dados
            return $arrRetorno;
        } catch (Exception $ex) { }
    }
    
    /**
     * Verifica se existe o e-mail se sim altera a senha e envia para o email
     * 
     * @param array $arrDados
     * @return unknown|mixed
     */
    function enviarSenha(array $arrDados){
        try {
            // Cast no valor para garantir a integridade
            $strEmail = $arrDados["strEmail"];
            $this->sql ="SELECT
                          u.id,
                          u.email,
                          u.nome
                        FROM usuario u
                        WHERE
                          u.email = :email";
            $this->prepare();
            $this->bind("email", $strEmail);
            $this->executar();
            $arrRetorno = $this->buscarDoResultadoAssoc(true);
            // Caso encontre o usuário pelo email            
            if(!empty($arrRetorno)){
                $strNovaSenha     = date("dmYs");
                $this->sql ="UPDATE usuario
                         SET senha = :senha
                         WHERE id = :id";
                $this->prepare();
                $this->bind("senha", md5($strNovaSenha));
                $this->bind("id",    $arrRetorno['id']);
                $this->executar();
                $intAlterados = $this->rowCount();
                $arrRetorno["novaSenha"] = $strNovaSenha;
                // Caso tenha alteração
                return ($intAlterados > 0) ? $arrRetorno : false;
            }else{
                throw new Exception("Usuário não encontrado");
            }
        } catch (Exception $ex) { }
    }
    
    /**
     * Verifica se o cpf já está cadastrado
     * 
     * @param array $arrDados
     * @return boolean
     */
    function existeCPF(array $arrDados){
        try {
            $strCPF = (string) $arrDados["strCPF"];
            $this->sql ="SELECT
                            u.id
                            FROM usuario u
                         WHERE
                            u.login = :login ";
            $this->prepare();
            $this->bind("login", $strCPF);
            $this->executar();
            $arrRetorno = $this->buscarDoResultadoAssoc(true);
            return (!empty($arrRetorno));
        } catch (Exception $ex) { }
    }
    
    /**
     * Verifica se o email já está cadastrado
     *
     * @param array $arrDados
     * @return boolean
     */
    function existeEmail(array $arrDados){
        try {
            $strEmail = $arrDados["strEmail"];
            $this->sql ="SELECT
                            u.id
                         FROM usuario u
                         WHERE
                            u.email = :email";
            $this->prepare();
            $this->bind("email", $strEmail);
            $this->executar();
            $arrRetorno = $this->buscarDoResultadoAssoc(true);
            return (!empty($arrRetorno));
        } catch (Exception $ex) { }
    }
}