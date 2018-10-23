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
}