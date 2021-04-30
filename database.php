<?php
class DatabaseConnector {

    private $dbConnection = null;

    public function __construct()
    {
        $host = "localhost";
        $db_name = "scim";
        $port = "3306";
        $user = "root";
        $pass = "";

        try {
            $this->dbConnection = new \PDO(
                "mysql:host=$host;port=$port;charset=utf8mb4;dbname=$db_name",
                $user,
                $pass
            );
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

    public function getConnection()
    {
        return $this->dbConnection;
    }
}
?>