<?php

/**
 * Controlador Exame
 *
 * @author Régis Perez
 */
class Consulta {
    
    /**
     * Irá conter o objeto  daoConsulta
     *
     * @var daoConsulta
     */
    private $objDaoConsulta;
    
    public function get_listarAreas(){
        // Criando o dao
        $this->objDaoConsulta = new daoConsulta();
    
        return $this->objDaoConsulta->listarAreas();
    }
    
    public function get_listarTiposExames(){
        // Criando o dao
        $this->objDaoConsulta = new daoConsulta();
    
        return $this->objDaoConsulta->listarTiposExames();
    }
    
    /**
     * Método que irá listar os totais exames por área
     * 
     * @return mixed
     */
    public function get_listarTotalExamePorArea(){
        // Criando o dao
        $this->objDaoConsulta = new daoConsulta();
        // Recuperando o total
        $arrDados = $this->objDaoConsulta->listarTotalExamePorArea();
        $arrRetorno = array();
        // Formatando o retorno
        foreach($arrDados as $intChave=>$arrValor){
            $arrRetorno[$intChave][] = $arrValor["descricao"];
            $arrRetorno[$intChave][] = $arrValor["total"];
        }
        return $arrRetorno;
    }
    
    /**
     * Método que irá listar os totais de exames por tipo
     * 
     * @return mixed
     */
    public function get_listarTotalExamePorTipoExame(){
        // Criando o dao
        $this->objDaoConsulta = new daoConsulta();
        // Recuperando os totais
        $arrDados = $this->objDaoConsulta->listarTotalExamePorTipoExame();
        $arrRetorno = array();
        // Formatando o retorno
        foreach($arrDados as $intChave=>$arrValor){
            $arrRetorno[$intChave][] = $arrValor["descricao"];
            $arrRetorno[$intChave][] = $arrValor["total"];
        }
        return $arrRetorno;
    }
      
    /**
     * Método que irá realizar a validação e o cadastro dos exames do paciente
     * 
     * @throws Exception
     * @return boolean
     */
    public function post_cadastrarAgendamento(){
        // Criando o dao
        $this->objDaoConsulta   = new daoConsulta();
        // Validando os dados postados
        if(empty($_POST["dadosAgendamento"])) throw new Exception("Dados Não Informados!");
        // Recuperando os dados do paciente
        $objAgendamento = json_decode($_POST["dadosAgendamento"]);  
        $objAgendamento->usuario_id = (int) $_POST["usuario_id"];
        // Validando os dados postados
        $this->validarCadastroAgendamento($objAgendamento); 
        // Cadastrando o exame
        $bolCadastro = $this->objDaoConsulta->cadastrarAgendamento($objAgendamento);// cadastrando o exame na base
        if(!$bolCadastro) throw new Exception("Não foi possível cadastrar o Agendamento!");
        
        return $this->notificarEquipe($objAgendamento);
    }
    
    /**
     * Método que irá disparar notificações para equipe médica
     * 
     * @param agendamento cadastrado na base $objAgendamento
     */
    function notificarEquipe($objAgendamento){
        $this->objDaoUsuario    = new daoUsuario();
        $objPaciente = (object) $this->objDaoUsuario->getUsuarioPorId($objAgendamento->usuario_id);
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
            "headings" => array("en" => "Solicititação de Agendamento"),
            'contents' => array("en" => "Paciente com o nº do PEP: $objPaciente->numero_pep, Solicitou um agendamento para o dia $objAgendamento->data_solicitada"),
            'data' => array(
                                "foo"=>"bar",
                                "acao"=>Constantes::$ULR_AGENDAMENTO_CONSULTA,
                                "parametros"=>array("agendamentoID"=>$objAgendamento->id)
                            )
        );
        // enviando a notificação e retornando o resultado
        return Utilidades::enviarNotificacao($arrDadosNotificacao);
    }
    /**
     * Método que irá realiza a confirmação de recebimento do exame do paciente
     * 
     * @throws Exception
     * @return boolean
     */
    public function post_confirmarRecebimento(){
        // Criando o dao
        $this->objDaoConsulta = new daoConsulta();
        // Validando os dados postados
        if(empty($_POST["dadosAgendamento"])) throw new Exception("Dados Não Informados!");
        if(empty($_POST["intIdUsuario"])) throw new Exception("Usuário Não Informados!");
        if(empty($_POST["intIdExame"])) throw new Exception("Exame Não Informados!");
        // Recuperando os dados do paciente
        $objAgendamento = json_decode($_POST["dadosAgendamento"]);
        $objAgendamento->usuario_id = (int) $_POST["intIdUsuario"];
        $objAgendamento->id = (int) $_POST["intIdExame"];
        $objAgendamentoBanco = (object) $this->objDaoConsulta->getExamePorId($objAgendamento->id);
        $objAgendamento->data_exame = $objAgendamentoBanco->data_exame;
        // Validando os dados postados
        $this->validarConfirmacaoExame($objAgendamento);
        // Cadastrando o exame
        $bolCadastro = $this->objDaoConsulta->confirmarRecebimento($objAgendamento);// cadastrando o exame na base
        if(!$bolCadastro) throw new Exception("Não foi possível cadastrar o exame!");
        return true;
    }
    
    /**
     * Método que irá validar o recebimento do exame
     * 
     * @param stdClass $objAgendamento
     * @throws Exception
     */
    function validarConfirmacaoExame(stdClass $objAgendamento){
        // Validação dos dados de exame
        if(!isset($objAgendamento->data_recebimento))                        throw new Exception("Data Recebimento Não Informada!");
        if(empty($objAgendamento->usuario_id))                               throw new Exception("Usuário Não Informado!");
        if(!Utilidades::validarData($objAgendamento->data_recebimento))      throw new Exception("Data Recebimento Inválida!");
        if(!Utilidades::diffData($objAgendamento->data_exame, Utilidades::formatarDataPraBanco($objAgendamento->data_recebimento)))         
            throw new Exception("Data Do Recebimento Tem que Ser Menor que a Coleta!");
        //validando data do recebimento
        if(!Utilidades::diffData(Utilidades::formatarDataPraBanco($objAgendamento->data_recebimento), date("Y-m-d")))
            throw new Exception("Data de Recebimento Tem que Ser Menor ou Igual a Data de Hoje!");
        
    }
    
    /**
     * Método que irá validar os dados de cadastro do Agendamento
     * 
     * @param Object $objAgendamento
     * @throws Exception
     */
    function validarCadastroAgendamento(stdClass $objAgendamento){
        // Validação dos dados de exame
        if(empty($objAgendamento->data_solicitada))   throw new Exception("Data da Consulta Não Informada!");
        if(empty($objAgendamento->usuario_id))        throw new Exception("Paciente Não Informado!");        
        if(empty($objAgendamento->area_id))           throw new Exception("Área Não Informada!");
      
        if(!Utilidades::validarData($objAgendamento->data_solicitada))       throw new Exception("Data da Consulta Inválida!");
        // Validando as datas
        if(!Utilidades::diffData(Utilidades::formatarDataPraBanco($objAgendamento->data_solicitada), 
            Utilidades::formatarDataPraBanco(date('d/m/Y', strtotime('+2 months')))))         
                throw new Exception("Data de consulta fora do limite, favor selecionar um período de 2 (dois) meses!");
            
        if(!Utilidades::diffData(date("Y-m-d") ,
            Utilidades::formatarDataPraBanco($objAgendamento->data_solicitada)))
            throw new Exception("Data da Consulta tem que ser maior ou igual a hoje!");
    } 
    
    /**
     * Método que irá retornar os Agendamentos pelo id do paciente (usuário)
     * @throws Exception
     * @return mixed
     */
    public function get_listarAgendamentosDoUsuarioPorId(){
        // Criando o dao
        $this->objDaoConsulta = new daoConsulta();
        // Validando os dados postados
        if(empty($_GET["intIdUsuario"])) throw new Exception("Id Não Informado!");
        // Recuperando os dados do paciente
        $intIdUsuario = (int) $_GET["intIdUsuario"];
        // Validações
        if($intIdUsuario == 0) throw new Exception("Usuário Inválido!");
        // Listando os exames do paciente
        $arrAgendamentos = $this->objDaoConsulta->listarAgendamentosDoPaciente($intIdUsuario);
        if(empty($arrAgendamentos)) throw new Exception("Agendamentos não foram Encontrados!");        
        // Retornando a lista de exames do paciente
        return $arrAgendamentos;
    }
    
    
    public function post_filtrarExames(){
        // Criando o dao
        $this->objDaoConsulta = new daoConsulta();        
         // Validando os filtros
         if(empty($_POST["filtroBusca"])) throw new Exception("Dados Não Informados!");
         // Recuperando os filtros
        $objFiltro = json_decode($_POST["filtroBusca"]);
        // Buscando os exames com os filtros recuperados
        $arrAgendamentos = $this->objDaoConsulta->filtrarExames((array) $objFiltro);
        if(empty($arrAgendamentos)) throw new Exception("Nenhum Exame Encontrado!");
        // formatando os exames
        foreach($arrAgendamentos as $inChave => $arrExame){
            $straAtrasado = ($arrExame["data_recebimento"] == null) ? "exame_atrasado" : "exame_entregue";
            // Formatando o nome do paciente
            $arrExame["nome"]           = "<a class='links link {$straAtrasado}' href='".Constantes::$ULR_DETALHE_EXAME.$arrExame["id"]."'>".$arrExame["nome"]."</a>";
            $arrExame["dias_atraso"]    = "<a class='links link {$straAtrasado}' href='".Constantes::$ULR_DETALHE_EXAME.$arrExame["id"]."'>".$arrExame["dias_atraso"]."</a>";
            $arrAgendamentos[$inChave] = $arrExame;
        }
        // Retornando a lista de exames filtrados
        return $arrAgendamentos;
    }
    
    /**
     * Método que irá retornar o exame pelo id
     *
     * @throws Exception
     * @return mixed
     */
    public function get_recuperarExamePorID(){
        // Criando o dao
        $this->objDaoConsulta = new daoConsulta();
        // Validando
        if(empty($_GET["intIdExame"])) throw new Exception("Exame Não Informados!");
        $intIdExame = (int) $_GET["intIdExame"];
        // Recuperando o exame da base
        $objAgendamento = (object) $this->objDaoConsulta->getExamePorId($intIdExame);
        if(!$objAgendamento) throw new Exception("Exame Não Encontrado!");
        // Formatando as tadas
        $objAgendamento->data_exame = Utilidades::formatarDataPraBr($objAgendamento->data_exame);
        $objAgendamento->data_previsao = Utilidades::formatarDataPraBr($objAgendamento->data_previsao);
        // Caso o exame esteja entregue
        if($objAgendamento->situacao ==1) $objAgendamento->data_recebimento = Utilidades::formatarDataPraBr($objAgendamento->data_recebimento);
        
        return $objAgendamento;
    }
}
