<?php
/**
 * Controlador Notifica��o
 *
 * @author Alberto Medeiros
 */
class Notificacao {
    
    /**
     * Ir� conter o objeto  daoNotificacao
     *
     * @var daoNotificacao
     */
    private $objDaoNotificacao;
    
    /**
     * 
     * @return mixed
     */
    public function get_listaCancer(){
        // Criando o dao
        $this->objDaoNotificacao = new daoNotificacao();
        // 
        return $this->objDaoNotificacao->listaCancer();
    }
    
    /**
     * 
     * @return ArrayObject
     */
    public function get_listaPerfis(){
        // Criando o dao
        $this->objDaoNotificacao = new daoNotificacao();
        
        return $this->objDaoNotificacao->listaPerfil();
    }
    
    /**
     * M�todo que ir� retornar o total de usu�rios do filtro 
     * 
     * @throws Exception
     * @return mixed
     */
    public function post_filtrarTotalUsuariosEnvio(){
        // Criando o dao
        $this->objDaoNotificacao = new daoNotificacao();
        // Validando os dados postados
        if(empty($_POST["dadosNotificacao"])) throw new Exception("Dados N�o Informados!");
        // Recuperando os dados da notifica��o
        $objNotificacao = json_decode($_POST["dadosNotificacao"]);
        // Recuperando o usu�rios que ser�o enviados
        $arrTotal = $this->objDaoNotificacao->getUsuariosEnviosFiltro((array) $objNotificacao, true);
        // Retornando o total de usu�rios
        return $arrTotal;
    }
    
    /**
     * M�todo que ir� realizar a valida��o e o cadastro das notificações
     * 
     * @throws Exception
     * @return boolean
     */
    public function post_cadastrarNotificacao(){
        // Criando o dao
        $this->objDaoNotificacao = new daoNotificacao();
        // Validando os dados postados
        if(empty($_POST["dadosNotificacao"])) throw new Exception("Dados N�o Informados!");
        // Recuperando os dados da notifica��o
        $objNotificacao = json_decode($_POST["dadosNotificacao"]);  
        // Validando os dados postados
        $this->validarCadastro($objNotificacao); 
        // Recuperando o usu�rios que ser�o enviados 
        $arrUsuarios = $this->objDaoNotificacao->getUsuariosEnviosFiltro((array) $objNotificacao, false);
        // Formatando o filtro que foi usado
        $objNotificacao->filtro = json_encode($objNotificacao);
        // // cadastrando de notifica��o na base
        $bolCadastro = $this->objDaoNotificacao->cadastrarNotificacao($objNotificacao, $arrUsuarios);
        if(!$bolCadastro) throw new Exception("N�o foi poss�vel cadastrar a notifica��o!");
        // Ids Usuario
        $arrIds = array();
        // Formatando os ids para envio em massa
        foreach($arrUsuarios as $arrValor){
            $arrIds[] = $arrValor["codigo_onesignal"];
        }
        // Criando os dados de notifica��o
        $arrDadosNotificacao = array(
            'include_player_ids' => $arrIds,
            "headings" => array("en" => $objNotificacao->titulo),
            'contents' => array("en" => $objNotificacao->corpo)
        );
        // Enviando a notifica��o
        $objRerotno = Utilidades::enviarNotificacao($arrDadosNotificacao);
        // Retornando sucesso
        return true;
    }
    
    /**
     * M�todo que ir� validar os dados de cadastro do Exame
     * 
     * @param Object $objExame
     * @throws Exception
     */
    function validarCadastro(stdClass $objNotificacao){
        // Valida��o dos dados de exame
        if(empty($objNotificacao->titulo))      throw new Exception("T�tulo da notifica��o n�o informado!");
        if(empty($objNotificacao->corpo))       throw new Exception("Corpo da notifica��o n�o informado!"); 
        if(strlen($objNotificacao->corpo) > 144)throw new Exception("Corpo da notifica��o n�o informado!");
    } 
    
    /**
     * M�todo que ir� retornar os exames pelo id do paciente (usu�rio)
     * 
     * @throws Exception
     * @return mixed
     */
    public function get_notificacoesDoUsuarioPorId(){
        // Criando o dao
        $this->objDaoNotificacao = new daoNotificacao();
        // Validando os dados postados
        if(empty($_GET["intIdUsuario"])) throw new Exception("Id N�o Informado!");
        $intIdUsuario = (int) $_GET["intIdUsuario"];
        // Valida��es
        if($intIdUsuario == 0) throw new Exception("Usu�rio Inv�lido!");
        // Listando os exames do paciente
        $arrExames = $this->objDaoNotificacao->listarNotificacoesDoPaciente($intIdUsuario);
        if(empty($arrExames)) throw new Exception("Exames n�o foram Encontrados!");        
        // Retornando a lista de exames do paciente
        return $arrExames;
    }
    
    /**
     * M�todo que ir� retornar os exames pelo id do paciente (usu�rio)
     *
     * @throws Exception
     * @return mixed
     */
    public function post_notificacoesLidas(){
        // Criando o dao
        $this->objDaoNotificacao = new daoNotificacao();
        // Validando os dados postados
        if(empty($_GET["intIdUsuario"])) throw new Exception("Id N�o Informado!");
        $intIdUsuario = (int) $_GET["intIdUsuario"];
        // Valida��es
        if($intIdUsuario == 0) throw new Exception("Usu�rio Inv�lido!");
        $this->objDaoNotificacao->notificacoesLidas($intIdUsuario);
        return true;
    }
    
    /**
     * M�todo que ir� listar as notifica��e da base
     * 
     * @throws Exception
     */
    public function post_filtrarNotificacoes(){
        // Criando o dao
        $this->objDaoNotificacao = new daoNotificacao();        
         // Validando os filtros
         if(empty($_POST["filtroBusca"])) throw new Exception("Dados N�o Informados!");
         // Recuperando os filtros
        $arrFiltro = json_decode($_POST["filtroBusca"]);
        // Buscando os exames com os filtros recuperados
        $arrExames = $this->objDaoNotificacao->filtrarNotificacoes((array) $arrFiltro);
        if(empty($arrExames)) throw new Exception("Nenhum Exame Encontrado!");
        // formatando os exames
        foreach($arrExames as $inChave => $arrExame){
            $straAtrasado = ($arrExame["data_recebimento"] == null) ? "exame_atrasado" : "exame_entregue";
            // Formatando o nome do paciente
            $arrExame["nome"]           = "<a class='links link {$straAtrasado}' href='".Constantes::$ULR_DETALHE_EXAME.$arrExame["id"]."'>".$arrExame["nome"]."</a>";
            $arrExame["dias_atraso"]    = "<a class='links link {$straAtrasado}' href='".Constantes::$ULR_DETALHE_EXAME.$arrExame["id"]."'>".$arrExame["dias_atraso"]."</a>";
            $arrExames[$inChave] = $arrExame;
        }
        // Retornando a lista de exames filtrados
        return $arrExames;
    }
    
    /**
     * M�todo que ir� retornar o exame pelo id
     *
     * @throws Exception
     * @return mixed
     */
    public function get_recuperarExamePorID(){
        // Criando o dao
        $this->objDaoNotificacao = new daoNotificacao();
        // Validando
        if(empty($_GET["intIdNotificacao"])) throw new Exception("Notifica��o N�o Informados!");
        $intIdNotificacao = (int) $_GET["intIdNotificacao"];
        // Recuperando o Notifica��o da base
        $objNotificacao = (object) $this->objDaoNotificacao->getExamePorId($intIdNotificacao);
        if(!$objNotificacao) throw new Exception("Exame N�o Encontrado!");
        // Formatando as datas
        $objNotificacao->data_envio = Utilidades::formatarDataPraBr($objNotificacao->data_envio);
        // Retornando a notifica��o
        return $objNotificacao;
    }
    
}