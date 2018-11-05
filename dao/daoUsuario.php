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
    
    function listaPerfil(){
        try {
            // Filtrando todos os cancers
            $this->sql ="SELECT
                          *
                        FROM perfil";
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
                          u.senha = :senha
                          and 
                          u.ativo = 1";
            $this->prepare();
            $this->bind("login", $strLogin);
            $this->bind("senha", $strSenha);
            $this->executar();
            $arrRetorno = $this->buscarDoResultadoAssoc(true);
            if(!empty($arrRetorno)){
                // Formatando o retorno
                $arrRetorno["strCpf"] = $arrRetorno["login"];
                try {
                    // Atualizando as informações do usuário
                    $this->sql ="UPDATE usuario
                         SET codigo_onesignal = :codigo_onesignal, ultimo_acesso = :ultimo_acesso
                         WHERE id = :id";
                    $this->prepare();
                    $this->bind("codigo_onesignal", $arrDados["strCodigoOnesignal"]);
                    $this->bind("ultimo_acesso",    date("Y-m-d H:i:s"));
                    $this->bind("id",               $arrRetorno["id"]);
                    $this->executar();
                } catch (Exception $e) { }
               
                
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
     * Método que irá atualizar o usuário 
     *  
     * @param int $intCpf
     * @throws Exception
     * @return mixed
     */
    function ativarPaciente($intCpf){
        try {
            // Cast no valor para garantir a integridade
            $intCpf = (int) $intCpf;
            $this->sql ="SELECT
                          u.id,
                          u.email,
                          u.nome, 
                          u.codigo_onesignal
                        FROM usuario u
                        WHERE
                          u.login = :login";
            $this->prepare();
            $this->bind("login", $intCpf);
            $this->executar();
            $arrRetorno = $this->buscarDoResultadoAssoc(true);
            // Caso encontre o usuário pelo email
            if(!empty($arrRetorno)){
                $this->sql ="UPDATE usuario
                         SET ativo = :ativo
                         WHERE id = :id";
                $this->prepare();
                $this->bind("ativo", 1);
                $this->bind("id",  $arrRetorno['id']);
                $this->executar();
                $intAlterados = $this->rowCount();
                if($intAlterados > 0) throw new Exception("Não foi possível atualizar!");
                // Caso tenha alteração
                return $arrRetorno;
            }else{
                throw new Exception("Usuário não encontrado!");
            }
        } catch (Exception $ex) { }
    }
    
    /**
     * Método que irá realizar o filtro dos usuários do sistema
     * 
     * @param array $arrDados
     * @return mixed
     */
    function pesquisarUsuarios(array $arrDados){
        try {
            $intPerfilID = (int) $arrDados["perfil_id"];
            $this->sql ="SELECT
                            u.id,
                            u.login,
                            u.nome
                         FROM usuario u
                         WHERE
                            u.perfil_id = :perfil_id ";
            
            if(isset($arrDados["cpf"]) && !empty($arrDados["cpf"]))
                $this->sql .= " AND login = :cpf";
            
            if(isset($arrDados["situacao"]) && $arrDados["situacao"] != "")
                $this->sql .= " AND ativo = :situacao";
            
            if(isset($arrDados["pep"]) && !empty($arrDados["pep"]))
                $this->sql .= " AND numero_pep = :pep";
            
            $this->prepare();
            $this->bind("perfil_id", $intPerfilID);
            
            if(isset($arrDados["cpf"]) && !empty($arrDados["cpf"]))
                $this->bind("cpf", $arrDados["cpf"]);
            
            if(isset($arrDados["situacao"]) && $arrDados["situacao"] != "")
                $this->bind("situacao", $arrDados["situacao"]);
                
            if(isset($arrDados["pep"]) && !empty($arrDados["pep"]))
                $this->bind("pep", $arrDados["pep"]);
            $this->executar();
            return $this->buscarDoResultadoAssoc();
        } catch (Exception $ex) { }
    }
    
    /**
     * Método que irá retornar o usuário pelo id
     * 
     * @param int $intIdUsuario
     * @throws Exception
     * @return mixed
     */
    function getUsuarioPorId($intIdUsuario){
        try {
            // Realizando um cast para garantir a integridade
            $intIdUsuario = (int) $intIdUsuario;
            $this->sql ="SELECT
                            *
                         FROM usuario u
                         WHERE
                            u.id = :id ";
            $this->prepare();
            $this->bind("id", $intIdUsuario);
            $this->executar();
            $arrUsuario = $this->buscarDoResultadoAssoc(true);
            if(empty($arrUsuario)) throw new Exception("Usuário Não Encontrado!");
            $arrUsuario["cpf"] = $arrUsuario["login"];
            $arrUsuario["data_nascimento"] = Utilidades::formatarDataPraBr($arrUsuario["data_nascimento"]);
            // Retornando o usuário
            return $arrUsuario;
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
    
    /**
     * Método que irá editar o usuário na base de dados
     * 
     * @param stdClass $objUsuario
     * @throws Exception
     * @return boolean
     */
    function cadastrarEditarUsuario(stdClass &$objUsuario){
        try {
            $strNovaSenha = $objUsuario->senha;
            $this->iniciarTransacao();
            $this->sql ="
                
                       UPDATE usuario
                       SET  perfil_id = :perfil_id,
                            nome = :nome,
                            endereco = :endereco,
                            data_nascimento = :data_nascimento, 
                            numero_pep = :numero_pep,
                            contato = :contato,
                            sexo = :sexo,
                            email = :email,
                            ativo = :ativo, 
                            login = :cpf,
                            cancer_id = :cancer_id,
                            contato_dois = :contato_dois, 
                            uf = :uf,
                            cidade = :cidade,
                            data_alteracao = :data_alteracao
                       WHERE id = :id";
            // Preparando a consulta
            $this->prepare();
            // Realizando os bids para segurança
            $this->bind("id", $objUsuario->id);
            $this->bind("perfil_id", $objUsuario->perfil_id);
            $this->bind("nome", $objUsuario->nome);
            $this->bind("endereco", @$objUsuario->endereco);
            $this->bind("data_nascimento", $objUsuario->data_nascimento);
            $this->bind("numero_pep", @$objUsuario->numero_pep);
            $this->bind("contato", @$objUsuario->contato);
            $this->bind("sexo", $objUsuario->sexo);
            $this->bind("email", $objUsuario->email);
            $this->bind("cpf", $objUsuario->cpf);
            $this->bind("ativo", $objUsuario->ativo);
            $this->bind("cancer_id", $objUsuario->cancer_id);
            $this->bind("contato_dois", @$objUsuario->contato_dois);
            $this->bind("uf", @$objUsuario->uf);
            $this->bind("data_alteracao", date("Y-m-d H:i:s"));
            $this->bind("cidade", @$objUsuario->cidade);
            // Recuperando o id do usuário cadastrado
            $this->executar();
            $this->comitarTransacao();
            // Verificando se houve alterações
            return ($this->rowCount() > 0);
        } catch (Exception $ex) {$this->desfazerTransacao(); throw new Exception($ex->getMessage()); }
    }
    
    /**
     * Método que irá cadastrar o usuário
     *
     * @param stdClass $objUsuario
     * @throws Exception
     * @return boolean
     */
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
                            codigo_onesignal,
                            uf,
                            cidade
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
                            :codigo_onesignal,
                            :uf,
                            :cidade
                        )
                        ";
            // Preparando a consulta
            $this->prepare();
            // Realizando os bids para segurança
            $this->bind("perfil_id", $objUsuario->perfil_id);
            $this->bind("nome", $objUsuario->nome);
            $this->bind("endereco", @$objUsuario->endereco);
            $this->bind("data_nascimento", $objUsuario->data_nascimento);
            $this->bind("numero_pep", @$objUsuario->pep);
            $this->bind("contato", @$objUsuario->contato);
            $this->bind("sexo", $objUsuario->sexo);
            $this->bind("email", $objUsuario->email);
            $this->bind("cpf", $objUsuario->cpf);
            $this->bind("senha", md5($objUsuario->senha));
            $this->bind("cancer_id", $objUsuario->cancer_id);
            $this->bind("contato_dois", @$objUsuario->contato_dois);
            $this->bind("codigo_onesignal", $objUsuario->onesignal);
            $this->bind("uf", @$objUsuario->uf);
            $this->bind("cidade", @$objUsuario->cidade);
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