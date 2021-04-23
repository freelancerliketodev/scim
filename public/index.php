<?php
require "../bootstrap.php";

use Src\Controller\DataController;

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode( '/', $uri );


if ($uri[1] !== 'api') {
    header("HTTP/1.1 404 Not Found");
    exit();
}

$action = $uri[2];

$possibleActions = array('Users','Token');
if (!isset($action,$possibleActions)) {
    header("HTTP/1.1 404 Not Found");
    exit();
}
if($action == "Token"){
    $secret = (array) json_decode(file_get_contents('php://input'), TRUE);

    if(!isset($secret[0]) || $secret[0] != getenv('SECRET_KEY')){
        header('HTTP/1.1 400 unauthorized');
        echo json_encode(array('error'=>'unauthorized'));
        return;
    }else{
        header('HTTP/1.1 200');
        echo json_encode(array('token'=>getenv('TEST_TOKEN')));
        return;  
    }
}

if (! authenticate()) {
    header("HTTP/1.1 401 Unauthorized");
    exit('Unauthorized');
}

// $data = (array) json_decode(file_get_contents('php://input'), TRUE);

$requestMethod = $_SERVER["REQUEST_METHOD"];
$controller = new DataController($dbConnection,$requestMethod);
$controller->processRequest();

function authenticate() {
    try {
        switch(true) {
            case array_key_exists('HTTP_AUTHORIZATION', $_SERVER) :
                $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
                break;
            case array_key_exists('Authorization', $_SERVER) :
                $authHeader = $_SERVER['Authorization'];
                break;
            default :
                $authHeader = null;
                break;
        }
        preg_match('/Bearer\s(\S+)/', $authHeader, $matches);
        if(!isset($matches[1])) {
            header('HTTP/1.1 400 unauthorized');
            echo json_encode(array('error'=>'No Bearer Token'));
            return;
        }
        if($matches[1] != getenv('TEST_TOKEN')){
            header('HTTP/1.1 400 unauthorized');
            echo json_encode(array('error'=>'Unauthorized'));
            return;
        }
        return true;
    } catch (\Exception $e) {
        return false;
    }
}

