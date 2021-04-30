<?php
    function abort($code,$type,$message = false){
        header("HTTP/1.1 ".$code." ".$type);
        if($message){
            echo json_encode(array('error'=>$message));
        }
        exit();
    }
?>