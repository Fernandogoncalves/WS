<?php
/**
 * Dao Padrão das notificações
 */
require_once(__DIR__ . '../../control/Notificacao.php');
require_once(__DIR__ . '/dao.php');

/**
 * Description of daoNotificacao
 *
 * @author Régis Perez
 */
class daoNotificacao extends Dao {


    function __construct() {
        parent::__construct();
    }
    
    /**
     * Método que irá listar as áreas
     * 
     * @return mixed
     */
    function listarAreas(){
        try {
            // Filtrando todos os cancers
            $this->sql ="SELECT
                          *
                        FROM area";
            $this->prepare();
            $this->executar();
            // Retornando a lista de cancer
            return $this->buscarDoResultadoAssoc();
        } catch (Exception $ex) { }
    }
    
    /**
     * Listar Tipo Cancer
     * 
     * @return ArrayObject
     */
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
     * Listar Perfis
     * 
     * @return ArrayObject
     */
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
     * Método que irá cadastrar a notificação
     * 
     * @param stdClass $objNotificacao
     * @param array $arrUsuarios
     * @throws Exception
     * @return boolean
     */
    function cadastrarNotificacao(stdClass &$objNotificacao, $arrUsuarios){
        try {
            $this->iniciarTransacao();
            $this->sql ="INSERT INTO notificacao
                        (
                            data_envio, 
                            titulo, 
                            mensagem, 
                            filtro
                        )
                        VALUES
                        (
                            :data_envio, 
                            :titulo, 
                            :mensagem, 
                            :filtro
                        )";
            // Preparando a consulta
            $this->prepare();
            // Realizando os binds para segurança
            $this->bind("data_envio", date("Y-m-d"));
            $this->bind("titulo", $objNotificacao->titulo);
            $this->bind("mensagem", $objNotificacao->corpo);
            $this->bind("filtro", $objNotificacao->filtro);  
            // Recuperando o id da notificação cadastrada
            $this->executar();
            // Recuperar id da notificação
            $objNotificacao->id = $this->retornarUltimoIDInserido();
            // Para cada usuário
            foreach($arrUsuarios as $intChave => $arrUsuario){
                $this->sql ="INSERT INTO usuario_notificacao
                        (
                            usuario_id,
                            notificacao_id
                        )
                        VALUES
                        (
                            ".$arrUsuario["id"].",
                            {$objNotificacao->id}
                        )";
                // Preparando a consulta
                $this->prepare();
                // Recuperando o id da notificação cadastrado
                $this->executar();
            }
            // Comitando a transação
            $this->comitarTransacao();
            // Verificando se houve alteraçõeses
            return ($this->rowCount() > 0);
        } catch (Exception $ex) {$this->desfazerTransacao(); throw new Exception($ex->getMessage(), 9999); }
    }
    
    /**
     * Método que irá filtrar os usuários que irão receber as notificações
     * 
     * @param array $arrDados
     * @param boolean $bolTotal
     * @throws Exception
     * @return mixed
     */
    function getUsuariosEnviosFiltro($arrDados, $bolTotal = false){
        // Filtra os exames de um determinado pep
        try{
            // Caso seja o total
            if($bolTotal){
                $this->sql ="SELECT
                              count(id) total
                            FROM usuario
                            WHERE
                                 1 = 1  ";
            }else{
                // listando os ids a serem enviados
                $this->sql ="SELECT
                              id,
                              codigo_onesignal
                            FROM usuario
                            WHERE
                                 1 = 1  ";
            }
            /***** FILTROS CASO INFORMADOS ******/
            if(isset($arrDados["perfil_id"]) && !empty($arrDados["perfil_id"]))
                $this->sql .= " AND perfil_id = " . (int) $arrDados["perfil_id"];
        
            if(isset($arrDados["sexo"]) && !empty($arrDados["sexo"]))
                $this->sql .= " AND sexo = " . (int) $arrDados["sexo"];
        
            if(isset($arrDados["idade"]) && !empty($arrDados["idade"]))
                $this->sql .= " AND TIMESTAMPDIFF(YEAR,data_nascimento,NOW()) >= " . (int) $arrDados["idade"];
    
            if(isset($arrDados["cidade"]) && !empty($arrDados["cidade"]))
                $this->sql .= " AND cidade = :cidade";
            
            if(isset($arrDados["pep"]) && !empty($arrDados["pep"]))
                $this->sql .= " AND numero_pep = " . ((int) $arrDados["pep"]);
            
            if(isset($arrDados["cancer_id"]) && !empty($arrDados["cancer_id"])){
                if((count($arrDados["cancer_id"]) == 1 && $arrDados["cancer_id"][0] > 0) || count($arrDados["cancer_id"]) > 1){
                    if(!in_array(8, $arrDados["cancer_id"])) $arrDados["cancer_id"][] = 8;
                    foreach($arrDados["cancer_id"] as $intChave => $intValue){
                        $arrDados["cancer_id"][$intChave] = (int) $intValue;
                    }
                    $this->sql .= " AND cancer_id in ( ".implode(",", $arrDados["cancer_id"])." ) ";
                }
            }
                
            // PREPARANDO A CONSULTA
            $this->prepare();
            
            // EXECUTANDO A CONSULTA
            $this->executar();
            $arrUsuarios = $this->buscarDoResultadoAssoc();
            if(empty($arrUsuarios)) throw new Exception("Usuários não foram encontrados!");
            // Retornando os exames filtrados
            return $arrUsuarios;
        } catch (Exception $ex) { throw new Exception($ex->getMessage(), 9999); }
    }
    
    /**
     * Método que irá retornar a notificação  pelo id
     * 
     * @param int $intIdExame
     * @return mixed
     */
    function getNotificacaoPorId($intIdN){
        try {
            $intIdN = (int) $intIdN;
            // Filtrando todos os cancers
            $this->sql ="SELECT 
                            * 
                        FROM 
                            notificacao 
                        WHERE id = :id";
            $this->prepare();
            $this->bind("id", $intIdN);
            $this->executar();
            // Retornando a lista de cancer
            return $this->buscarDoResultadoAssoc(true);
        } catch (Exception $ex) { }
    }

    /**
     * Método que irá retornar as notificações pelo id do paciente (usuario)
     * 
     * @param int $intIdUsuario
     * @throws Exception
     * @return mixed
     */
    function listarNotificacoesDoPaciente($intIdUsuario){
        try {
            // Realizando um cast para garantir a integridade
            $intIdUsuario = (int) $intIdUsuario;
            $this->sql ="SELECT
                          *
                        FROM usuario_notificacao un
                        JOIN notificacao n on
                              n.id = un.notificacao_id
                              and un.usuario_id = :usuario_id
                        ORDER BY notificacao_id DESC";
            $this->prepare();
            $this->bind("usuario_id", $intIdUsuario);
            $this->executar();
            $arrNotificacoes = $this->buscarDoResultadoAssoc();
            if(empty($arrNotificacoes)) throw new Exception("Notificações não foram encontradas!");
            // Para cada notificação 
            foreach($arrNotificacoes as $intChave => $arrNotificacao){
                // Formatando as fatas
                $arrNotificacoes[$intChave]["data_envio"] = Utilidades::formatarDataPraBr($arrNotificacao["data_envio"]);
            }
            // Retornando as notificações do usuário
            return $arrNotificacoes;
        } catch (Exception $ex) { }
    }
    
    /**
     * Método que irá registrar as mensagens do usuários que foram lidas
     * 
     * @param int $intIdUsuario
     * @throws Exception
     * @return mixed
     */
    function notificacoesLidas($intIdUsuario){
        try {
            // Realizando um cast para garantir a integridade
            $intIdUsuario = (int) $intIdUsuario;
            $this->sql ="UPDATE usuario_notificacao
                        SET visualizou = 1, data_leitura = :data
                        WHERE usuario_id = :usuario_id AND visualizou = 0";
            $this->prepare();
            $this->bind("usuario_id", $intIdUsuario);
            $this->bind("data", date("Y-m-d H:i:s"));
            $this->executar();
        } catch (Exception $ex) { }
    }

    /**
     * Método que irá retornar as notificações enviadas
     * 
     * @param array $arrDados
     * @throws Exception
     * @return mixed
     */
    function filtrarNotificacoes(array $arrDados){
        //filtra os exames de um determinado pep
        try{
            $this->sql ="SELECT
                          *
                        FROM notificacao
                        WHERE     1 = 1  ";
            
            /***** FILTROS CASO INFORMADOS ******/
            if(isset($arrDados["data_envio"]) && !empty($arrDados["data_envio"]))
                $this->sql .= " AND data_envio = :data_envio";
            
            if(isset($arrDados["titulo"]) && !empty($arrDados["titulo"]))
                $this->sql .= " AND titulo LIKE :titulo";
            
            // PREPARANDO A CONSULTA
            $this->prepare();
            /***** BIND NOS VALORES DOS FILTROS ******/
            if(isset($arrDados["data_envio"]) && !empty($arrDados["data_envio"]))
                $this->bind("data_envio", Utilidades::formatarDataPraBanco($arrDados["data_envio"]));
            
            if(isset($arrDados["titulo"]) && !empty($arrDados["titulo"]))
                 $this->bind("titulo", "%".$arrDados["titulo"]."%");
            
            // EXECUTANDO A CONSULTA
            $this->executar();
            $arrNotificacoes = $this->buscarDoResultadoAssoc();
            if(empty($arrNotificacoes)) throw new Exception("Não foi encontrado notificações!");
            // Retornando os exames filtrados
            return $arrNotificacoes;
        } catch (Exception $ex) { throw new Exception($ex->getMessage()); }
    } 
}
