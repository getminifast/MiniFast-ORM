<?php

namespace MiniFastORM;

class Database extends Singleton
{
    private $connection;
    
    public function connect(string $host, string $db_name, string $user, string $password)
    {
        $pdo_options[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;
        
        try {
            $this->connection = new PDO('mysql:host='.$host.';dbname='.$db_name.'', $user, $password, $pdo_options);
        } catch (Exception $e) {
            throw new Exception($e);
        }
    }
    
    public function export()
    {
        
    }
}