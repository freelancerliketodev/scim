<?php
require('auth.php');
require('errors.php');

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Const
const SECRET_KEY = 'SecureLogin';
const TEST_TOKEN = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJtZXNzYWdlIjoiSldUIFJ1bGVzISIsImlhdCI6MTQ1OTQ0ODExOSwiZXhwIjoxNDU5NDU0NTE5fQ.-yIVBD5b73C75osbmwwshQNRC7frWUYrqaTjTpza2y4';

// Parse url
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode( '/', $uri );

// Check path start with api
if ($uri[1] !== 'api') {
    abort(404,'Not found');
}

//Get action
$action = $uri[2];
$possibleActions = array('Users','Token','ResourceTypes');
if (!isset($action,$possibleActions)) {
    abort(404,'Not found');
}

// Get Token function
// api/Token -> return muck token, for now
// Not auth routes
if($action == "Token"){
    $secret = (array) json_decode(file_get_contents('php://input'), TRUE);

    if(!isset($secret[0]) || $secret[0] != SECRET_KEY){
        abort(400,'unauthorized');
    }else{
        header('HTTP/1.1 200');
        echo json_encode(array('token'=>TEST_TOKEN));
        return;  
    }
}
if (! authenticate()) {
    abort(401,'Unauthorized');
}

//load DB
require('database.php');
require('users.php');
$dbConnection = (new DatabaseConnector())->getConnection();

// Users endpoint
$requestMethod = $_SERVER["REQUEST_METHOD"];
$urlSecondPart = isset($uri[3]) ? '/'.$uri[3] : '';

$route = $requestMethod.":".$action.$urlSecondPart;

//
$response = false;
// All routes // Method:Entity/Action
switch($route){
    case 'POST:Users':
        $userModel = new Users($dbConnection);
        $response = $userModel->createUserFromRequest();
    break;
    case 'GET:ResourceTypes':
        $response['status_code_header'] = 'HTTP/1.1 200';
        $response['body'] = array('Users');
    break;
    case 'GET:Users':
        $response['status_code_header'] = 'HTTP/1.1 200';
    break;
}

if($response){
    header($response['status_code_header']);
    if (isset($response['body'])) {
        echo json_encode($response['body']);
    }
    exit();
}
abort(404,'Not Found');