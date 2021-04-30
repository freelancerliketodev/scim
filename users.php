<?php
class Users {

    private $db = null;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function createUserFromRequest()
    {
        $data = (array) json_decode(file_get_contents('php://input'), TRUE);

        if (! $this->validateUser($data)) {
            abort(422, 'Unprocessable Entity');
        }

        $primaryEmail = $this->getEmailFromData($data);
        $userExistId = $this->findByEmail($primaryEmail);

        if(!$userExistId){
            $result = (int)$this->insert($data); 
            if($result){
                $response['status_code_header'] = 'HTTP/1.1 201 Created';
                $response['body'] = array('id'=>$result);
                return $response;
            }else{
                abort(422, 'Unprocessable Entity', 'Invalid input');    
            }
        }else{
            $userId = $userExistId[0]['id'];
            $result = $this->update($userId,$data);
            
            if((int)$result){
                $response['status_code_header'] = 'HTTP/1.1 200 Updateded';
                $response['body'] = array('id'=>$userId);
                return $response;
            }else{
                abort(400, 'Unprocessable Entity', 'Nothing for update');
            }
        }
    }

    public function findByEmail($email){
        $statement = "SELECT id FROM users WHERE email = ?;";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array($email));
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

    public function insert(Array $data)
    {
        foreach($data['emails'] as $email){
            if($email['Primary']){
                $primaryEmail = $email['value'];    
            }
        }
        $name = $data['name']['formatted'];

        $statement = "
            INSERT INTO users 
                (email, name)
            VALUES
                (:email, :name);
        ";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                'email' => $primaryEmail,
                'name'  => $name
            ));
            $results = $statement->fetch(\PDO::FETCH_ASSOC);
            if($statement->rowCount()){
                return $this->db->lastInsertId();
            }
            return false;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }  
    }

    public function update($id, Array $data)
    {
        $name = $data['name']['formatted'];

        $statement = "
            UPDATE users
            SET 
                name = :name
            WHERE id = :id;
        ";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                'id' => $id,
                'name'  => $name
            ));
            
            return $statement->rowCount();
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }  
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
}
?>