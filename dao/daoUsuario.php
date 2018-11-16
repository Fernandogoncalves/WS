<?php
/**
 * Dao Padr�o dos usu�rios
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
     * M�todo de login do usu�rio
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
                    // Atualizando as informa��es do usu�rio
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
            // Caso encontre o usu�rio pelo email            
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
                // Caso tenha altera��o
                return ($intAlterados > 0) ? $arrRetorno : false;
            }else{
                throw new Exception("Usu�rio n�o encontrado");
            }
        } catch (Exception $ex) { }
    }
    
    /**
     * M�todo que ir� atualizar o usu�rio 
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
            // Caso encontre o usu�rio pelo email
            if(!empty($arrRetorno)){
                $this->sql ="UPDATE usuario
                         SET ativo = :ativo
                         WHERE id = :id";
                $this->prepare();
                $this->bind("ativo", 1);
                $this->bind("id",  $arrRetorno['id']);
                $this->executar();
                $intAlterados = $this->rowCount();
                if($intAlterados > 0) throw new Exception("N�o foi poss�vel atualizar!");
                // Caso tenha altera��o
                return $arrRetorno;
            }else{
                throw new Exception("Usu�rio n�o encontrado!");
            }
        } catch (Exception $ex) { }
    }
    
    /**
     * M�todo que ir� realizar o filtro dos usu�rios do sistema
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
     * M�todo que ir� retornar o usu�rio pelo id
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
            if(empty($arrUsuario)) throw new Exception("Usu�rio N�o Encontrado!");
            $arrUsuario["cpf"] = $arrUsuario["login"];
            $arrUsuario["data_nascimento"] = Utilidades::formatarDataPraBr($arrUsuario["data_nascimento"]);
            // Retornando o usu�rio
            return $arrUsuario;
        } catch (Exception $ex) { }
    }
    
    /**
     * Verifica se o cpf j� est� cadastrado
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
     * Verifica se o email j� est� cadastrado
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
     * Verifica se o email j� est� cadastrado
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
     * M�todo que ir� editar o usu�rio na base de dados
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
            // Realizando os bids para seguran�a
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
            // Recuperando o id do usu�rio cadastrado
            $this->executar();
            $this->comitarTransacao();
            // Verificando se houve altera��es
            return ($this->rowCount() > 0);
        } catch (Exception $ex) {$this->desfazerTransacao(); throw new Exception($ex->getMessage()); }
    }
    
    /**
     * M�todo que ir� cadastrar o usu�rio
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
            // Realizando os binds para seguran�a
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
            // Recuperando o id do usu�rio cadastrado
            $this->executar();
            // Recuperar id do usu�rio
            $objUsuario->id = $this->retornarUltimoIDInserido();
            $this->comitarTransacao();
            // Verificando se houve altera��es
            return ($this->rowCount() > 0);
        } catch (Exception $ex) {$this->desfazerTransacao(); throw new Exception($ex->getMessage()); }
    }

    function carregarCidades($uf){
        try {
            $this->sql ="SELECT
                          c.nome,                          
                        FROM
                          cidade c
                        INNER JOIN estado e on c.estado = e.id
                        WHERE e.uf=uf";
            $this->prepare();
            $this->bind("uf", $uf);
            $this->executar();
            // Retornando a lista de cidades do estado selecionado
            return $this->buscarDoResultadoAssoc();
        } catch (Exception $ex) { }
    }

}