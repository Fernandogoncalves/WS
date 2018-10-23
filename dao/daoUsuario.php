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
    
    function listaCancer(){
        try {
            // Filtrando todos os cancers
            $this->sql ="SELECT
                          *
                        FROM cancer";
            $this->prepare();
            $this->executar();
            // Retornando a lista de cancer
            return $this->buscarDoResultadoAssoc();
        } catch (Exception $ex) { }
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
    
    /**
     * Verifica se o email já está cadastrado
     *
     * @param array $arrDados
     * @return boolean
     */
    function getIdsOnesignalPorPefil($intIDPerfil){
        try {
            $intIDPerfil =(int) $intIDPerfil;
            $this->sql ="SELECT
                            u.codigo_onesignal
                         FROM usuario u
                         WHERE
                            ativo = 1
                            and u.perfil_id = :perfil_id";
            $this->prepare();
            $this->bind("perfil_id", $intIDPerfil);
            $this->executar();
            return $this->buscarDoResultadoAssoc();
        } catch (Exception $ex) { }
    }
    
    function cadastrarUsuario(stdClass &$objUsuario){
        try {
            $strNovaSenha = $objUsuario->senha;
            $this->iniciarTransacao();
            $this->sql ="INSERT INTO usuario
                        (
                            perfil_id, 
                            nome, 
                            endereco, 
                            data_nascimento, 
                            numero_pep, 
                            contato, 
                            sexo, 
                            email, 
                            ativo, 
                            login, 
                            senha, 
                            cancer_id, 
                            contato_dois, 
                            codigo_onesignal
                        )
                        VALUES
                        (
                            :perfil_id,
                            :nome,
                            :endereco,
                            :data_nascimento,
                            :numero_pep,
                            :contato,
                            :sexo,
                            :email,
                            0,
                            :cpf,
                            :senha,
                            :cancer_id,
                            :contato_dois, 
                            :codigo_onesignal
                        )
                        ";
            // Preparando a consulta
            $this->prepare();
            // Realizando os bids para segurança
            $this->bind("perfil_id", $objUsuario->perfil_id);
            $this->bind("nome", $objUsuario->nome);
            $this->bind("endereco", $objUsuario->endereco);
            $this->bind("data_nascimento", $objUsuario->data_nascimento);
            $this->bind("numero_pep", $objUsuario->pep);
            $this->bind("contato", $objUsuario->contato);
            $this->bind("sexo", $objUsuario->sexo);
            $this->bind("email", $objUsuario->email);
            $this->bind("cpf", $objUsuario->cpf);
            $this->bind("senha", md5($objUsuario->senha));
            $this->bind("cancer_id", $objUsuario->cancer_id);
            $this->bind("contato_dois", @$objUsuario->contato_dois);
            $this->bind("codigo_onesignal", $objUsuario->onesignal);
            // Recuperando o id do usuário cadastrado
            $this->executar();
            // Recuperar id do usuário
            $objUsuario->id = $this->retornarUltimoIDInserido();
            $this->comitarTransacao();
            // Verificando se houve alterações
            return ($this->rowCount() > 0);
        } catch (Exception $ex) {$this->desfazerTransacao(); throw new Exception($ex->getMessage()); }
    }
}