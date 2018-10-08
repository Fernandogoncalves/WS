<?php
session_start();
ini_set("session.cookie_lifetime","3600");

/**
 * Verificação de acesso
 */
if(!isset($_SESSION["usuario"])){
    try {
        if(isset($_GET["usuario"]) && isset($_GET["senha"])){
            if($_GET["usuario"] == "utilizador" && $_GET["senha"] = "pwdws$123xy"){
                $_SESSION["usuario"]    = "utilizador";
                $_SESSION["acesso"]     = true;
            }else throw new Exception("Erro na autenticação, favor verificar usuário e senha!");
        }else{
            throw new Exception("Você não tem permissão de acesso de acesso!");
        }
    } catch (Exception $e) {
        // this session has worn out its welcome; kill it and start a brand new one
        session_unset();
        session_destroy();
        $arrMensagem = array("result" => array("mensagem"=>htmlentities($e->getMessage())));
        echo json_encode($arrMensagem);
        die;
    }
}

ini_set('memory_limit', '1024M');
date_default_timezone_set('America/Sao_Paulo');
require __DIR__.'/control/controle.php';


require 'Slim/Slim.php';
\Slim\Slim::registerAutoloader();
$app = new \Slim\Slim(array(
    'debug' => true
        ));

$app->contentType("application/json");

$app->error(function ( Exception $e = null) use ($app) {
         echo '{"error":{"text":"'. $e->getMessage() .'"}}';
        });

//GET pode possuir um parametro na URL
$app->get('/:controller/:action(/:parameter)', function ($controller, $action, $parameter = null) use($app) {
            
            include_once "control/{$controller}.php";
            $classe = new $controller();
            $retorno = call_user_func_array(array($classe, "get_" . $action), array($parameter));
            echo '{"result":' . json_encode($retorno) . '}';
        });

//POST nÃ£o possui parÃ¢metros na URL, e sim na requisiÃ§Ã£o

$app->post('/:controller/:action', function ($controller, $action) use ($app) {
    
            $request = json_decode(\Slim\Slim::getInstance()->request()->getBody());           
            include_once "control/{$controller}.php";
            $classe = new $controller();
            
            $retorno = call_user_func_array(array($classe, "post_" . $action), array($request));
             echo '{"result":' . json_encode($retorno) . '}';       });

$app->run();