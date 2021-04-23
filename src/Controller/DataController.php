<?php

namespace Src\Controller;

use Src\TableGateways\UserGateway;

class DataController {

    private $db;
    private $requestMethod;

    private $userGateway;

    public function __construct($db,$requestMethod)
    {
        $this->db = $db;
        $this->requestMethod = $requestMethod;
        $this->userGateway = new UserGateway($db);
    }

    public function processRequest()
    {
        switch ($this->requestMethod) {
            case 'POST':
                $response = $this->createUserFromRequest();
            break;
        }

        header($response['status_code_header']);
        if ($response['body']) {
            echo json_encode($response['body']);
        }
    }

    private function createUserFromRequest()
    {
        $data = (array) json_decode(file_get_contents('php://input'), TRUE);

        if (! $this->validateUser($data)) {
            return $this->unprocessableEntityResponse();
        }

        $primaryEmail = $this->getEmailFromData($data);
        $userExistId = $this->userGateway->findByEmail($primaryEmail);

        if(!$userExistId){
            $result = $this->userGateway->insert($data);
            
            if((int)$result){
                $response['status_code_header'] = 'HTTP/1.1 201 Created';
                $response['body'] = array('id'=>$result);
                return $response;
            }else{
                return $this->unprocessableEntityResponse();
            }
        }else{
            $userId = $userExistId[0]['id'];
            $result = $this->userGateway->update($userId,$data);
            
            if((int)$result){
                $response['status_code_header'] = 'HTTP/1.1 200 Updateded';
                $response['body'] = array('id'=>$userId);
            }else{
                $response['status_code_header'] = 'HTTP/1.1 400';
                $response['body'] = array('error'=>"Nothing for update");
            }    
            return $response;
        }
    }

    private function getEmailFromData($data){
        $primaryEmail = false;
        if(isset($data['emails'])){
            foreach($data['emails'] as $email){
                if($email['Primary']){
                    $primaryEmail = $email['value'];    
                }
            }
        }
        return $primaryEmail;
    }

    private function validateUser($data)
    {
        $primaryEmail = $this->getEmailFromData($data);
        if (!$primaryEmail) {
            return false;
        }
        if (! isset($data['name']['formatted'])) {
            return false;
        }
        return true;
    }

    private function unprocessableEntityResponse()
    {
        $response['status_code_header'] = 'HTTP/1.1 422 Unprocessable Entity';
        $response['body'] = json_encode([
            'error' => 'Invalid input'
        ]);
        return $response;
    }

    private function notFoundResponse()
    {
        $response['status_code_header'] = 'HTTP/1.1 404 Not Found';
        $response['body'] = null;
        return $response;
    }
}