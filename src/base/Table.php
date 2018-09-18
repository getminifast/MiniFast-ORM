<?php

namespace MiniFastORM;

class Table
{
    private $name;
    private $columns = [];
    private $types = [
        'INTEGER', 'INT', 'SMALLINT', 'TINYINT', 'MEDIUMINT', 'BIGINT',
        'DECIMAL', 'NUMERIC',
        'FLOAT', 'DOUBLE',
        'DATE', 'TIME', 'DATETIME', 'TIMESTAMP', 'YEAR',
        'CHAR', 'VARCHAR', 'BINARY', 'VARBINARY', 'BLOB', 'TEXT', 'ENUM', 'SET'
    ];
    
    public function setTableName(string $name)
    {
        $this->name = $name;
    }
    
    public function hasColumn(string $name, string $type, $size, array $attributes = [])
    {
        if(!empty($name) and !empty($type) and in_array(strtoupper($type), $this->types;)) {
            $this->columns[$name] = [];
        } else {
            throw new Exception('Column name and type cannot be empty.');
        }
    }
}