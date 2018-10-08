<?php

class Utilidades {
    
    function tiraMoeda($valor) {
        $pontos = array("_", ".");
        $result = str_replace($pontos, "", $valor);

        return $result;
    }

}
