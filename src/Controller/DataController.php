<?php

namespace Src\Controller;

class DataController {
    private $requestMethod;

    public function __construct($requestMethod)
    {
        $this->requestMethod = $requestMethod;
    }

    public function processRequest()
    {
        switch ($this->requestMethod) {
            case 'GET':
                $response = $this->test();
            break;
        }

        header($response['status_code_header']);
        if ($response['body']) {
            echo json_encode($response['body']);
            exit();
        }
    }

    private function test()
    {
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = 'ok';
        return $response;
    }
}