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
    
    
    
    function cadastrarExame(stdClass &$objExame){
        try {
            $this->iniciarTransacao();
            $this->sql ="INSERT INTO exame
                        (
                            data_exame, 
                            data_previsao,
                            data_recebimento,
                            usuario_id,
                            tipo_exame_id,
                            area_id
                        )
                        VALUES
                        (
                            :data_exame, 
                            :data_previsao,
                            :data_recebimento,
                            :usuario_id,
                            :tipo_exame_id,
                            :area_id
                        )
                        ";
            // Preparando a consulta
            $this->prepare();
            // Realizando os bids para seguran�a
            $this->bind("data_exame", $objExame->data_exame);
            $this->bind("data_previsao", $objExame->data_previsao);
            $this->bind("data_recebimento", $objExame->data_recebimento);
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
     * M�todo que ir� retornar os exames pelo id do paciente (usuario)
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
                            e.data_previsao
                         ASC
                            ,e.data_recebimento
                         ASC
                        ";
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
