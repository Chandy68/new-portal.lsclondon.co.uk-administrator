<?php

class Database
{

    protected $config;
    private $host, $username, $password, $database;
    private $connection;
    private $mdlConnection;

    public function __construct()
    {
        $this->config = parse_ini_file($_SERVER['DOCUMENT_ROOT'] . '/administrator/config.ini');
        $this->host = $this->config['dbHost'];
        $this->username = $this->config['dbUsername'];
        $this->password = $this->config['dbPassword'];
        $this->database = $this->config['dbName'];
    }

    private function connect_moodle()
    {
        $host = $this->config['mdlHost'];
        $database = $this->config['mdlName'];
        $username = $this->config['mdlUsername'];
        $password = $this->config['mdlPassword'];
        try {
            $this->mdlConnection = new PDO("mysql:host=$host;dbname=$database", $username, $password, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
            $this->mdlConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
        }
        return $this->mdlConnection;
    }

    private function connect()
    {
        try {
            $this->connection = new PDO("mysql:host=$this->host;dbname=$this->database", $this->username, $this->password, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
        }
        return $this->connection;
    }

    public function executeQuery($sql)
    {
        $connection = $this->connect();
        $statement = $connection->prepare($sql);
        $statement->execute();
        $statement->setFetchMode(PDO::FETCH_ASSOC);
        return $statement->fetchAll();
    }

    public function execute_update($sql,$data)
    {
        $connection = $this->connect();
        $statement = $connection->prepare($sql);
        $statement->execute($data);
    }

    public function executeMdlQuery($sql)
    {
        $connection = $this->connect_moodle();
        $statement = $connection->prepare($sql);
        $statement->execute();
        $statement->setFetchMode(PDO::FETCH_ASSOC);
        return $statement->fetchAll();
    }

    public function executeSqlQuery($sql)
    {
        $connection = $this->connect();
        $connection->exec($sql);
    }
    
    /* These are not being used, can be delated later:19-04-2023
    public function executeUpdateMdlCourseId($mdl_course_id,$tsl_id)
    {
        $sql="UPDATE termsubjectlecturer set mdl_course_id=? where id=?";
        $connection = $this->connect();
        $statement = $connection->prepare($sql);
        $statement->execute([$mdl_course_id,$tsl_id]);
    }

    public function executeUpdateMdlUserId($mdl_user_id,$sid)
    {
        $sql="UPDATE student set mdl_user_id=? where id=?";
        $connection = $this->connect();
        $statement = $connection->prepare($sql);
        $statement->execute([$mdl_user_id,$sid]);
    }
    */
}
