<?php
/**
 * Controlador Notificação
 *
 * @author Alberto Medeiros
 */
class Notificacao {
    
    /**
     * Irá conter o objeto  daoNotificacao
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
     * Método que irá retornar o total de usuários do filtro 
     * 
     * @throws Exception
     * @return mixed
     */
    public function get_filtrarTotalUsuariosEnvio(){
        // Criando o dao
        $this->objDaoNotificacao = new daoNotificacao();
        // Validando os dados postados
        if(empty($_POST["dadosNotificacao"])) throw new Exception("Dados Não Informados!");
        // Recuperando os dados da notificação
        $objNotificacao = json_decode($_POST["dadosNotificacao"]);
        // Validando os dados postados
        $this->validarCadastro($objNotificacao);
        // Recuperando o usuários que serão enviados
        $arrTotal = $this->objDaoNotificacao->getUsuariosEnviosFiltro((array) $objNotificacao, true);
        // Retornando o total de usuários
        return $arrTotal;
    }
    
    /**
     * Método que irá realizar a validação e o cadastro dos da notificação
     * 
     * @throws Exception
     * @return boolean
     */
    public function post_cadastrarNotificacao(){
        // Criando o dao
        $this->objDaoNotificacao = new daoNotificacao();
        // Validando os dados postados
        if(empty($_POST["dadosNotificacao"])) throw new Exception("Dados Não Informados!");
        // Recuperando os dados da notificação
        $objNotificacao = json_decode($_POST["dadosNotificacao"]);  
        // Validando os dados postados
        $this->validarCadastro($objNotificacao); 
        // Recuperando o usuários que serão enviados 
        $arrUsuarios = $this->objDaoNotificacao->getUsuariosEnviosFiltro((array) $objNotificacao, false);
        // Formatando o filtro que foi usado
        $objNotificacao->filtro = json_encode($objNotificacao);
        // // cadastrando de notificação na base
        $bolCadastro = $this->objDaoNotificacao->cadastrarNotificacao($objNotificacao, $arrUsuarios);
        if(!$bolCadastro) throw new Exception("Não foi possível cadastrar a notificação!");
        // Ids Usuario
        $arrIds = array();
        // Formatando os ids para envio em massa
        foreach($arrUsuarios as $arrValor){
            $arrIds[] = $arrValor["codigo_onesignal"];
        }
        // Criando os dados de notificação
        $arrDadosNotificacao = array(
            'include_player_ids' => $arrIds,
            "headings" => array("en" => $objNotificacao->titulo),
            'contents' => array("en" => $objNotificacao->corpo)
        );
        // Enviando a notificação
        $objRerotno = Utilidades::enviarNotificacao($arrDadosNotificacao);
        // Retornando sucesso
        return true;
    }
    
    /**
     * Método que irá validar os dados de cadastro do Exame
     * 
     * @param Object $objExame
     * @throws Exception
     */
    function validarCadastro(stdClass $objNotificacao){
        // Validação dos dados de exame
        if(empty($objNotificacao->titulo))      throw new Exception("Título da notificação não informado!");
        if(empty($objNotificacao->corpo))       throw new Exception("Corpo da notificação não informado!"); 
        if(strlen($objNotificacao->corpo) > 144)throw new Exception("Corpo da notificação não informado!");
    } 
    
    /**
     * Método que irá retornar os exames pelo id do paciente (usuário)
     * 
     * @throws Exception
     * @return mixed
     */
    public function get_notificacoesDoUsuarioPorId(){
        // Criando o dao
        $this->objDaoNotificacao = new daoNotificacao();
        // Validando os dados postados
        if(empty($_GET["intIdUsuario"])) throw new Exception("Id Não Informado!");
        $intIdUsuario = (int) $_GET["intIdUsuario"];
        // Validações
        if($intIdUsuario == 0) throw new Exception("Usuário Inválido!");
        // Listando os exames do paciente
        $arrExames = $this->objDaoNotificacao->listarNotificacoesDoPaciente($intIdUsuario);
        if(empty($arrExames)) throw new Exception("Exames não foram Encontrados!");        
        // Retornando a lista de exames do paciente
        return $arrExames;
    }
    
    /**
     * Método que irá listar as notificaçõe da base
     * 
     * @throws Exception
     */
    public function post_filtrarNotificacoes(){
        // Criando o dao
        $this->objDaoNotificacao = new daoNotificacao();        
         // Validando os filtros
         if(empty($_POST["filtroBusca"])) throw new Exception("Dados Não Informados!");
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
     * Método que irá retornar o exame pelo id
     *
     * @throws Exception
     * @return mixed
     */
    public function get_recuperarExamePorID(){
        // Criando o dao
        $this->objDaoNotificacao = new daoNotificacao();
        // Validando
        if(empty($_GET["intIdNotificacao"])) throw new Exception("Notificação Não Informados!");
        $intIdNotificacao = (int) $_GET["intIdNotificacao"];
        // Recuperando o Notificação da base
        $objNotificacao = (object) $this->objDaoNotificacao->getExamePorId($intIdNotificacao);
        if(!$objNotificacao) throw new Exception("Exame Não Encontrado!");
        // Formatando as datas
        $objNotificacao->data_envio = Utilidades::formatarDataPraBr($objNotificacao->data_envio);
        // Retornando a notificação
        return $objNotificacao;
    }
    
}