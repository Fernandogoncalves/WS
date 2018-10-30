<?php
/**
 * Dao Padrão dos exames
 */

require_once(__DIR__ . '../../control/Exame.php');
require_once(__DIR__ . '/dao.php');

/**
 * Description of daoExame
 *
 * @author Régis Perez
 */
class daoExame extends Dao {


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
     * Método que irá listar os tipos de exames
     * 
     * @return mixed
     */
    function listarTiposExames(){
        try {
            // Filtrando todos os cancers
            $this->sql ="SELECT
                          *
                        FROM tipo_exame";
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
                          ROUND(SUM(TIMESTAMPDIFF(DAY,data_exame,data_recebimento)) / COUNT(ID), 0) AS media,
                          :data_coleta + INTERVAL ROUND(SUM(TIMESTAMPDIFF(DAY,data_exame,data_recebimento)) / COUNT(ID), 0) DAY AS previsao
                        FROM exame
                        WHERE 
                            tipo_exame_id = :tipo_exame_id 
                            AND data_exame >= NOW() - INTERVAL 120 DAY";
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
     * Método que irá cadastrar o exame
     * 
     * @param stdClass $objExame
     * @throws Exception
     * @return boolean
     */
    function cadastrarExame(stdClass &$objExame){
        try {
            $this->iniciarTransacao();
            $this->sql ="INSERT INTO exame
                        (
                            data_exame, 
                            data_previsao,
                            usuario_id,
                            tipo_exame_id,
                            area_id
                        )
                        VALUES
                        (
                            :data_exame, 
                            :data_previsao,
                            :usuario_id,
                            :tipo_exame_id,
                            :area_id
                        )
                        ";
            // Preparando a consulta
            $this->prepare();
            // Realizando os bids para segurança
            $this->bind("data_exame", $objExame->data_exame);
            $this->bind("data_previsao", Utilidades::formatarDataPraBanco($objExame->data_previsao));
            $this->bind("data_exame", Utilidades::formatarDataPraBanco($objExame->data_exame));
            $this->bind("usuario_id", $objExame->usuario_id);
            $this->bind("tipo_exame_id", $objExame->tipo_exame_id);
            $this->bind("area_id", $objExame->area_id);            
            
            // Recuperando o id do exame cadastrado
            $this->executar();
            // Recuperar id do exame
            $objExame->id = $this->retornarUltimoIDInserido();
            $this->comitarTransacao();
            // Verificando se houve altera��es
            return ($this->rowCount() > 0);
        } catch (Exception $ex) {$this->desfazerTransacao(); throw new Exception($ex->getMessage()); }
    }

    /**
     * Método que irá retornar os exames pelo id do paciente (usuario)
     * 
     * @param int $intIdUsuario
     * @throws Exception
     * @return mixed
     */
    function listarExamesDoPaciente($intIdUsuario){
        try {
            // Realizando um cast para garantir a integridade
            $intIdUsuario = (int) $intIdUsuario;
            $this->sql ="SELECT
                            *
                         FROM exame e
                         WHERE
                            e.usuario_id = :usuario_id 
                         ORDER BY 
                            e.data_previsao ASC,
                            e.data_recebimento ASC ";
            $this->prepare();
            $this->bind("usuario_id", $intIdUsuario);
            $this->executar();
            $arrExames = $this->buscarDoResultadoAssoc(true);
            if(empty($arrExames)) throw new Exception("Exames não foram encontrados!");
            // Retornando os exames do paciente
            return $arrExames;
        } catch (Exception $ex) { }
    }

    /**
     * M�todo que ir� retornar os exames filtrados
     * 
     * @param int $intIdArea,$intIdTipoExame,$intPep
     * @throws Exception
     * @return mixed
     */
    function filtrarExames($intIdArea,$intIdTipoExame,$pep){
        try {
            // Realizando um cast para garantir a integridade
            $intIdArea = (int) $intIdArea;
            $intIdTipoExame = (int) $intIdTipoExame;
           
            $this->sql ="SELECT
                            *
                         FROM exame e
                         INNER JOIN usuario u
                         ON
                         e.usuario_id = u.id
                         WHERE
                            e.area_id = :area_id
                         AND
                            e.tipo_exame_id = :tipo_exame_id
                         AND
                            u.numero_pep= :numero_pep

                         
                        ";
            $this->prepare();
            $this->bind("area_id", $intIdArea);
            $this->bind("tipo_exame_id", $intIdTipoExame);
            $this->bind("numero_pep", $pep);
            $this->executar();
            $arrExames = $this->buscarDoResultadoAssoc(true);
            if(empty($arrExames)) throw new Exception("Exames não foram encontrados!");
            // Retornando os exames filtrados
            return $arrExames;
        } catch (Exception $ex) { }
    }
}
