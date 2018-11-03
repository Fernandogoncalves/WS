<?php

/**
 * Controlador Exame
 *
 * @author Régis Perez
 */
class Exame {
    
    /**
     * Irá conter o objeto  daoExame
     *
     * @var daoExame
     */
    private $objDaoExame;
    
    public function get_listarAreas(){
        // Criando o dao
        $this->objDaoexame = new daoExame();
    
        return $this->objDaoexame->listarAreas();
    }
    
    public function get_listarTiposExames(){
        // Criando o dao
        $this->objDaoexame = new daoExame();
    
        return $this->objDaoexame->listarTiposExames();
    }
      
    /**
     * Método que irá realizar a validação e o cadastro dos exames do paciente
     * 
     * @throws Exception
     * @return boolean
     */
    public function post_cadastrarExame(){
        // Criando o dao
        $this->objDaoexame = new daoExame();
        // Validando os dados postados
        if(empty($_POST["dadosExame"])) throw new Exception("Dados Não Informados!");
        // Recuperando os dados do paciente
        $objExame = json_decode($_POST["dadosExame"]);  
        $objExame->usuario_id = (int) $_POST["usuario_id"];
        // Validando os dados postados
        $this->validarCadastroExame($objExame); 
        // Cadastrando o exame
        $bolCadastro = $this->objDaoexame->cadastrarExame($objExame);// cadastrando o exame na base
        if(!$bolCadastro) throw new Exception("Não foi possível cadastrar o exame!");
        return true;
    }
    
    /**
     * Método que irá realiza a confirmação de recebimento do exame do paciente
     * 
     * @throws Exception
     * @return boolean
     */
    public function post_confirmarRecebimento(){
        // Criando o dao
        $this->objDaoexame = new daoExame();
        // Validando os dados postados
        if(empty($_POST["dadosExame"])) throw new Exception("Dados Não Informados!");
        if(empty($_POST["intIdUsuario"])) throw new Exception("Usuário Não Informados!");
        if(empty($_POST["intIdExame"])) throw new Exception("Exame Não Informados!");
        // Recuperando os dados do paciente
        $objExame = json_decode($_POST["dadosExame"]);
        $objExame->usuario_id = (int) $_POST["intIdUsuario"];
        $objExame->id = (int) $_POST["intIdExame"];
        $objExameBanco = (object) $this->objDaoexame->getExamePorId($objExame->id);
        $objExame->data_exame = $objExameBanco->data_exame;
        // Validando os dados postados
        $this->validarConfirmacaoExame($objExame);
        // Cadastrando o exame
        $bolCadastro = $this->objDaoexame->confirmarRecebimento($objExame);// cadastrando o exame na base
        if(!$bolCadastro) throw new Exception("Não foi possível cadastrar o exame!");
        return true;
    }
    
    /**
     * Método que irá validar o recebimento do exame
     * 
     * @param stdClass $objExame
     * @throws Exception
     */
    function validarConfirmacaoExame(stdClass $objExame){
        // Validação dos dados de exame
        if(empty($objExame->usuario_id))                               throw new Exception("Usuário Não Informado!");
        if(!Utilidades::validarData($objExame->data_recebimento))      throw new Exception("Data Recebimento Inválida!");
        if(!Utilidades::diffData($objExame->data_exame, Utilidades::formatarDataPraBanco($objExame->data_recebimento)))         throw new Exception("Data Do Recebimento Tem que Ser Menor que a Coleta!");
        
        
    }
    
    /**
     * Método que irá validar os dados de cadastro do Exame
     * 
     * @param Object $objExame
     * @throws Exception
     */
    function validarCadastroExame(stdClass $objExame){
        // Validação dos dados de exame
        if(empty($objExame->data_exame))        throw new Exception("Data da Realização do Exame Não Informada!");
        if(empty($objExame->data_previsao))     throw new Exception("Data da Previsão do Exame Não Informada!");        
        if(empty($objExame->usuario_id))        throw new Exception("Paciente Não Informado!");        
        if(empty($objExame->tipo_exame_id))     throw new Exception("Tipo de exame Não Informado!");
        if(empty($objExame->area_id))           throw new Exception("Área Não Informada!");
      
        if(!Utilidades::validarData($objExame->data_exame))       throw new Exception("Data da Realização do Exame Inválida!");
        if(!Utilidades::validarData($objExame->data_previsao))    throw new Exception("Data da Previsão do Exame Inválida!");
        // Validando as datas
        if(!Utilidades::diffData(Utilidades::formatarDataPraBanco($objExame->data_exame), 
            Utilidades::formatarDataPraBanco($objExame->data_previsao)))         
                throw new Exception("Data de Coleta Tem que Ser Menor que a Previsão!");
    } 
    
    public function get_previsaoPorTipoExame(){
        // Criando o dao
        $this->objDaoexame = new daoExame();
        // Validando os dados postados
        if(empty($_GET["intIdTipoExame"])) throw new Exception("Tipo do Exame Não Informado!");
        if(empty($_GET["strDataColeta"])) throw new Exception("Data Coleta Não Informada!");
        // Recuperando os dados do paciente
        $intIdTipoExame = (int) $_GET["intIdTipoExame"];
        $strDataColeta  = $_GET["strDataColeta"];
        // Validações
        if($intIdTipoExame == 0) throw new Exception("Usuário Inválido!");
        // Listando os exames do paciente
        $arrPrevisao = $this->objDaoexame->getPrevisaoPorTipoExame($intIdTipoExame, $strDataColeta);
        if(empty($arrPrevisao) || $arrPrevisao["qtd_exames"] == 0) throw new Exception("Não existe uma previsão para esse tipo de exame, favor solicitar ao atendente um prazo e cadastrar manualmente!");
        // Retornando a lista de exames do paciente
        return $arrPrevisao;
    }
    
    /**
     * Método que irá retornar os exames pelo id do paciente (usuário)
     * @throws Exception
     * @return mixed
     */
    public function get_listarExamesDoUsuarioPorId(){
        // Criando o dao
        $this->objDaoexame = new daoExame();
        // Validando os dados postados
        if(empty($_GET["intIdUsuario"])) throw new Exception("Id Não Informado!");
        // Recuperando os dados do paciente
        $intIdUsuario = (int) $_GET["intIdUsuario"];
        // Validações
        if($intIdUsuario == 0) throw new Exception("Usuário Inválido!");
        // Listando os exames do paciente
        $arrExames = $this->objDaoexame->listarExamesDoPaciente($intIdUsuario);
        if(empty($arrExames)) throw new Exception("Exames não foram Encontrados!");        
        // Retornando a lista de exames do paciente
        return $arrExames;
    }
    
    
    public function post_filtrarExames(){
        // Criando o dao
        $this->objDaoexame = new daoExame();        
         // Validando os filtros
         if(empty($_POST["filtroBusca"])) throw new Exception("Dados Não Informados!");
         // Recuperando os filtros
        $objFiltro = json_decode($_POST["filtroBusca"]);
        // Buscando os exames com os filtros recuperados
        $arrExames = $this->objDaoexame->filtrarExames((array) $objFiltro);
        if(empty($arrExames)) throw new Exception("Nenhum Exame Encontrado!");
        // formatando os exames
        foreach($arrExames as $inChave => $arrExame){
            $straAtrasado = ($arrExame["data_recebimento"] == null) ? "exame_atrasado" : "exame_entregue";
            // Formatando o nome do paciente
            $arrExame["nome"]           = "<a class='links link {$straAtrasado}' href='".Constantes::$ULR_DETALHE_EXAME."/".$arrExame["id"]."'>".$arrExame["nome"]."</a>";
            $arrExame["dias_atraso"]    = "<a class='links link {$straAtrasado}' href='".Constantes::$ULR_DETALHE_EXAME."/".$arrExame["id"]."'>".$arrExame["dias_atraso"]."</a>";
            $arrExames[$inChave] = $arrExame;
        }
        // Retornando a lista de exames filtrados
        return $arrExames;
}
}
