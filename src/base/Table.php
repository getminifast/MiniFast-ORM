<?php

namespace MiniFastORM;

class Table
{
    protected $columns = [];
    protected $values = [];
    protected $table = '';
    protected $connection;
    protected $query = false;
    
    public function __call($name, $arguments)
    {
        if (strpos($name, 'set') === 0) {
            $name = substr($name, 0, 3);
            $name = unFormatName($name);
            
            $this->set($name, $value);
        }
    }
    
    protected function set(string $col, $value)
    {
        $this->columns[] = $col;
        $this->values[$col] = $value;
    }

    /*
     * Insert data into the database
     */
    public function save()
    {
        $database = Database::getInstance()->getPDO();
        
        if (!empty($this->table) and !empty($this->columns) and !empty($this->values)) {
            $query = 'INSERT INTO'
                . $this->table
                . '(' . implode(', ', $this->columns) . ') '
                . 'VALUES(:' . implode(', :', $this->columns) . ')';
            $req = $database->prepare($query);
            $req->execute($this->values);
        } else {
            throw new Exception('You cannot save before inserting data.');
        }
    }

    public static function now()
    {
        return date('AAA-MM-DD hh:mm:ss');
    }
    
    protected function unFormatName($name)
    {
        $exploded = preg_replace('/([A-Z])/', '_$1', $string);
        return strtolower($exploded);
    }
}