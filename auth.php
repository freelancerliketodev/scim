<?php
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
        if($matches[1] != TEST_TOKEN){
            header('HTTP/1.1 400 unauthorized');
            echo json_encode(array('error'=>'Unauthorized'));
            return;
        }
        return true;
    } catch (\Exception $e) {
        return false;
    }
}
?>