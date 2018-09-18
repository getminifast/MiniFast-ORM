<?php

namespace MiniFastORM;

class Database extends Singleton
{
    private $connection;
    
    public function connect(string $host, string $db_name, string $user, string $password)
    {
        $pdo_options[\PDO::ATTR_ERRMODE] = \PDO::ERRMODE_EXCEPTION;
        
        try {
            $this->connection = new \PDO('mysql:host='.$host.';dbname='.$db_name.'', $user, $password, $pdo_options);
        } catch (Exception $e) {
            throw new Exception($e);
        }
    }
    
    public function export($class)
    {
        $sql = '';
        $class->setTableDefinition();
        $class = $class->getTable();
        
        if (isset($class['name'])) {
            $sql .= "CREATE TABLE IF NOT EXISTS `". $class['name'] . "` (";
            $i = 0;
            
            foreach ($class['columns'] as $key => $col) {
                $sql .= ($i > 0 ? ", ": "")
                    . "`$key`"
                    . ' ' . $col['type']
                    . (!empty($col['size']) ? " (" . $col['size'] . ")":"");
                if (isset($col['attributes']['autoincrement'])) {
                    $sql .= ($col['attributes']['autoincrement'] ? ' AUTO_INCREMENT':'');
                }
                if (isset($col['attributes']['primarykey'])) {
                    $sql .= ($col['attributes']['primarykey'] ? ' PRIMARY KEY':'');
                }
                if (isset($col['attributes']['required'])) {
                    $sql .= ($col['attributes']['required'] ? ' NOT NULL':'');
                }
                if (isset($col['attributes']['default'])) {
                    $sql .= ' `' . $col['attributes']['default'] . '`';
                }
                $i++;
            }
            
            $sql .= ") ENGINE=InnoDB;\n";
        }
        
        $req = $this->connection->query($sql);
        
        return $sql;
    }
    
    public function getTable($table_name)
    {
        $namespace = '\MiniFastORM\Table\\';
        $full_class = $namespace . $table_name;
        $class = new $full_class();
        return $class;
    }
    
    public function getPDO()
    {
        return $this->connection;
    }
}