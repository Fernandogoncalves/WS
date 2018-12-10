<?php
/**
 * Dao Padrão dos exames
 */

require_once(__DIR__ . '../../control/Consulta.php');
require_once(__DIR__ . '/dao.php');

/**
 * Description of daoExame
 *
 * @author Régis Perez
 */
class daoConsulta extends Dao {


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
     * Método que irá listas o total de exames por área
     * 
     * @return mixed
     */
    function listarTotalConsultaPorArea(){
        try {
            $this->sql ="SELECT
                          a.descricao,
                          count(s.id) total
                        FROM
                          solicitacao_agendamento s
                        INNER JOIN area a on s.id = e.area_id
                        GROUP BY a.descricao
                        WHERE s.data_recebimento is null";
            $this->prepare();
            $this->executar();
            // Retornando a lista de cancer
            return $this->buscarDoResultadoAssoc();
        } catch (Exception $ex) { }
    }
    
    /**
     * Método que irá calcular a previsão de entrega para o tipo de exame
     * 
     * @param integer $intIdTipoExame
     * @return mixed
     */
    function getPrevisaoPorTipoExame($intIdTipoExame, $strDataColeta){
        try {
            // Formatando a data para o banco de dados
            $strDataColeta = Utilidades::formatarDataPraBanco($strDataColeta);
            // Filtrando todos os cancers
            $this->sql ="SELECT
                          SUM(TIMESTAMPDIFF(DAY,data_exame,data_recebimento)) AS total_dias,
                          COUNT(ID) AS qtd_exames,
                          ROUND(SUM(TIMESTAMPDIFF(DAY,data_exame,data_recebimento)) / COUNT(ID), 0) AS mediaCalculada,
                          :data_coleta + INTERVAL ROUND(SUM(TIMESTAMPDIFF(DAY,data_exame,data_recebimento)) / COUNT(ID), 0) DAY AS previsao
                        FROM exame
                        WHERE 
                            tipo_exame_id = :tipo_exame_id 
                            AND data_exame >= NOW() - INTERVAL 120 DAY
                            AND data_recebimento is not null";
            $this->prepare();
            // Realizando os bids para seguran�a
            $this->bind("tipo_exame_id", $intIdTipoExame);
            $this->bind("data_coleta", $strDataColeta);
            $this->executar();
            $arrPrevisao = $this->buscarDoResultadoAssoc(true);
            // Formatando a data
            if(!empty($arrPrevisao) && $arrPrevisao["qtd_exames"] > 0) $arrPrevisao["previsao"] = Utilidades::formatarDataPraBr($arrPrevisao["previsao"], 'Y-m-d');
            // Retornando a lista de cancer
            return $arrPrevisao;
        } catch (Exception $ex) {}
    }
    
    /**
     * Método que irá cadastrar o Agendamento
     * 
     * @param stdClass $objAgendamento
     * @throws Exception
     * @return boolean
     */
    function cadastrarAgendamento(stdClass &$objAgendamento){
        try {
            $this->iniciarTransacao();
            $this->sql ="INSERT INTO solicitacao_agendamento
                        (
                            data_solicitada, 
                            usuario_id, 
                            aceito, 
                            descricao, 
                            area_id
                        )
                        VALUES
                        (
                            :data_solicitada, 
                            :usuario_id,
                            0,
                            :descricao,
                            :area_id
                        )
                        ";
            // Preparando a consulta
            $this->prepare();
            // Realizando os bids para segurança
            $this->bind("data_solicitada", Utilidades::formatarDataPraBanco($objAgendamento->data_solicitada));
            $this->bind("usuario_id", $objAgendamento->usuario_id);
            $this->bind("descricao", $objAgendamento->descricao);
            $this->bind("area_id", $objAgendamento->area_id);            
            // Recuperando o id do exame cadastrado
            $this->executar();
            // Recuperar id do exame
            $objAgendamento->id = $this->retornarUltimoIDInserido();
            $this->comitarTransacao();
            // Verificando se houve altera��es
            return ($this->rowCount() > 0);
        } catch (Exception $ex) {$this->desfazerTransacao(); throw new Exception($ex->getMessage(), 9999); }
    }
    
    /**
     * Método que ira realizar o recebimento do exame
     * 
     * @param stdClass $objAgendamento
     * @throws Exception
     * @return boolean
     */
    function confirmarRecebimento(stdClass &$objAgendamento){
        try {
            $objAgendamento->data_recebimento = Utilidades::formatarDataPraBanco($objAgendamento->data_recebimento);
            $this->iniciarTransacao();
            $this->sql ="UPDATE exame
                        SET data_recebimento = :data_recebimento
                        WHERE 
                              id = :id ";
            // Preparando a consulta
            $this->prepare();
            // Realizando os bids para segurança
            $this->bind("data_recebimento", $objAgendamento->data_recebimento);
            $this->bind("id", $objAgendamento->id);
            // Recuperando o id do exame cadastrado
            $this->executar();
            $this->comitarTransacao();
            // Verificando se houve alterações
            return ($this->rowCount() > 0);
        } catch (Exception $ex) {$this->desfazerTransacao(); throw new Exception($ex->getMessage(), 9999); }
    }
    
    /**
     * Método que irá retornar o exame pelo id
     * 
     * @param unknown $intIdExame
     * @return mixed
     */
    function getExamePorId($intIdExame){
        try {
            $intIdExame = (int) $intIdExame;
            // Filtrando todos os cancers
            $this->sql ="SELECT
                        	e.*,
                        	a.descricao as area,
                        	tp.descricao as tipo_exame,
                        	u.nome,
                        	u.contato,
                        	u.contato_dois,
                        	u.numero_pep,
                        	CASE
                        	  WHEN data_recebimento IS NULL THEN 0
                        	  ELSE 1
                        	END AS situacao,
                        	TIMESTAMPDIFF(DAY,data_exame,
                        		(CASE
                        		  WHEN data_recebimento IS NOT NULL THEN data_recebimento
                        		  ELSE NOW()
                        		END)
                        	) dias_atraso
                         FROM exame e
                         INNER JOIN area a on a.id = e.area_id
                         INNER JOIN tipo_exame tp on tp.id = e.tipo_exame_id
                         INNER JOIN usuario u on u.id = e.usuario_id
                         WHERE e.id = :id ";
            $this->prepare();
            $this->bind("id", $intIdExame);
            $this->executar();
            // Retornando a lista de cancer
            return $this->buscarDoResultadoAssoc(true);
        } catch (Exception $ex) {   throw new Exception($ex->getMessage(), 9999);   }
    }

    /**
     * Método que irá retornar os agendamentos pelo id do paciente (usuario)
     * 
     * @param int $intIdUsuario
     * @throws Exception
     * @return mixed
     */
    function listarAgendamentosDoPaciente($intIdUsuario){
        try {
            // Realizando um cast para garantir a integridade
            $intIdUsuario = (int) $intIdUsuario;
            $this->sql ="SELECT
                          a.descricao area,
                          s.*
                        FROM
                          solicitacao_agendamento s
                        INNER JOIN area a on a.id = s.area_id
                        WHERE usuario_id = :usuario_id
                        ORDER BY s.data_solicitada desc ";
            $this->prepare();
            $this->bind("usuario_id", $intIdUsuario);
            $this->executar();
            $arrAgendamentos = $this->buscarDoResultadoAssoc();
            if(empty($arrAgendamentos)) throw new Exception("Consultas não foram encontradas!");
            // Para cada agendamento 
            foreach($arrAgendamentos as $intChave => $exames){
                // Formatando as fatas
                $arrAgendamentos[$intChave]["data_solicitada"] = Utilidades::formatarDataPraBr($exames["data_solicitada"]);
                $arrAgendamentos[$intChave]["data_agendamento"] = Utilidades::formatarDataPraBr($exames["data_agendamento"], 'Y-m-d H:i:s');
                // Se a data de confirmação não for vazia
                if(!empty($arrAgendamentos["data_confirmada"]) && $arrAgendamentos["data_confirmada"] != null){
                    $arrAgendamentos[$intChave]["data_confirmada"] = Utilidades::formatarDataPraBr($exames["data_confirmada"]);
                    $arrAgendamentos[$intChave]["data_solicitada"] = Utilidades::formatarDataPraBr($exames["data_confirmada"]);
                }
            }
            // Retornando os agendamentos do paciente
            return $arrAgendamentos;
        } catch (Exception $ex) {  throw new Exception($ex->getMessage(), 9999);  }
    }

    /**
     * M�todo que ir� retornar os exames filtrados
     * 
     * @param int $intIdArea,$intIdTipoExame,$intPep
     * @throws Exception
     * @return mixed
     */
    function filtrarExames(array $arrDados){
        //filtra os exames de um determinado pep
        try{
            $this->sql ="SELECT
                            e.*,
                            u.nome,
                            TIMESTAMPDIFF(DAY,data_exame,
                            (CASE
                              WHEN data_recebimento IS NOT NULL THEN data_recebimento
                              ELSE NOW()
                            END)
                            ) dias_atraso
                        FROM exame e
                        INNER JOIN usuario u ON e.usuario_id = u.id
                        WHERE
                             1 = 1  ";
            
            /***** FILTROS CASO INFORMADOS ******/
            if(isset($arrDados["area_id"]) && !empty($arrDados["area_id"]))
                $this->sql .= " AND e.area_id = :area_id";
            
            if(isset($arrDados["situacao"]) && $arrDados["situacao"] != ""){
                $strSituacao = ($arrDados["situacao"] == 0) ? " is null " : " is not null ";
                $this->sql .= " AND data_recebimento {$strSituacao}";
            }
            
            if(isset($arrDados["tipo_exame_id"]) && !empty($arrDados["tipo_exame_id"]))
                $this->sql .= " AND e.tipo_exame_id = :tipo_exame_id";
            
            if(isset($arrDados["pep"]) && !empty($arrDados["pep"]))
                $this->sql .= " AND u.numero_pep= :numero_pep";
            
            $this->sql .= "   
                  ORDER BY
                    TIMESTAMPDIFF(DAY,data_exame,
                    (CASE
                        WHEN data_recebimento IS NOT NULL THEN data_recebimento
                        ELSE NOW()
                        END)
                    ) DESC";
            // PREPARANDO A CONSULTA
            $this->prepare();
            /***** BIND NOS VALORES DOS FILTROS ******/
            if(isset($arrDados["area_id"]) && !empty($arrDados["area_id"]))
                $this->bind("area_id", $arrDados["area_id"]);
            
            if(isset($arrDados["tipo_exame_id"]) && !empty($arrDados["tipo_exame_id"]))
                 $this->bind("tipo_exame_id", $arrDados["tipo_exame_id"]);
        
            if(isset($arrDados["pep"]) && !empty($arrDados["pep"]))
                 $this->bind("numero_pep", $arrDados["pep"]);
            // EXECUTANDO A CONSULTA
            $this->executar();
            $arrAgendamentos = $this->buscarDoResultadoAssoc();
            if(empty($arrAgendamentos)) throw new Exception("Exames não foram encontrados!");
            // Retornando os exames filtrados
            return $arrAgendamentos;
        } catch (Exception $ex) { throw new Exception($ex->getMessage()); }
    } 
}
