<?php
namespace Src\TableGateways;

class UserGateway {

    private $db = null;

    public function __construct($db)
    {
        $this->db = $db;
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
}